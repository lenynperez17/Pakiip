from rest_framework import viewsets, permissions, filters, status
from rest_framework.decorators import action
from rest_framework.response import Response
from django_filters.rest_framework import DjangoFilterBackend
from django.db.models import Count, Sum, Avg, Q, F
from django.utils import timezone
from datetime import datetime, timedelta
from .models import (
    Report, ReportExecution, Dashboard, DashboardWidget,
    SalesReport, MarketingReport, SupportReport
)
from .serializers import (
    ReportSerializer, ReportExecutionSerializer, DashboardSerializer,
    DashboardWidgetSerializer, SalesReportSerializer,
    MarketingReportSerializer, SupportReportSerializer
)


class ReportViewSet(viewsets.ModelViewSet):
    """
    ViewSet for managing custom reports.
    """
    queryset = Report.objects.all()
    serializer_class = ReportSerializer
    permission_classes = [permissions.IsAuthenticated]
    filter_backends = [DjangoFilterBackend, filters.SearchFilter, filters.OrderingFilter]
    filterset_fields = ['report_type', 'format_type', 'is_public']
    search_fields = ['name', 'description']
    ordering_fields = ['created_at', 'execution_time_avg']
    ordering = ['-created_at']

    def get_queryset(self):
        queryset = super().get_queryset()
        
        # Filter reports user has access to
        if not self.request.user.is_staff:
            queryset = queryset.filter(
                Q(is_public=True) |
                Q(created_by=self.request.user) |
                Q(allowed_users=self.request.user)
            ).distinct()
        
        return queryset

    def perform_create(self, serializer):
        serializer.save(created_by=self.request.user)

    @action(detail=True, methods=['post'])
    def execute(self, request, pk=None):
        """Execute a report with given parameters."""
        report = self.get_object()
        parameters = request.data.get('parameters', {})
        
        # Validate date range if required
        if report.date_range_required:
            if not parameters.get('start_date') or not parameters.get('end_date'):
                return Response({
                    'error': 'Este reporte requiere un rango de fechas'
                }, status=400)
        
        # Create execution record
        start_time = timezone.now()
        
        try:
            # In a real implementation, this would execute the SQL query
            # For now, we'll simulate execution
            import time
            time.sleep(0.5)  # Simulate query execution
            
            # Simulated results
            results = {
                'columns': report.columns or ['Column1', 'Column2', 'Column3'],
                'data': [
                    ['Value1', 'Value2', 'Value3'],
                    ['Value4', 'Value5', 'Value6'],
                ],
                'total_rows': 2
            }
            
            # Calculate execution time
            execution_time = (timezone.now() - start_time).total_seconds()
            
            # Create execution record
            execution = ReportExecution.objects.create(
                report=report,
                executed_by=request.user,
                parameters_used=parameters,
                execution_time=execution_time,
                rows_returned=results['total_rows'],
                status='success'
            )
            
            # Update report average execution time
            report.last_executed = timezone.now()
            executions = report.executions.filter(status='success')
            report.execution_time_avg = executions.aggregate(
                avg=Avg('execution_time')
            )['avg'] or 0
            report.save()
            
            return Response({
                'execution_id': execution.id,
                'results': results,
                'execution_time': execution_time
            })
            
        except Exception as e:
            # Log error
            execution = ReportExecution.objects.create(
                report=report,
                executed_by=request.user,
                parameters_used=parameters,
                execution_time=(timezone.now() - start_time).total_seconds(),
                status='error',
                error_message=str(e)
            )
            
            return Response({
                'error': 'Error ejecutando el reporte',
                'details': str(e)
            }, status=500)

    @action(detail=True, methods=['post'])
    def share(self, request, pk=None):
        """Share report with specific users."""
        report = self.get_object()
        
        if report.created_by != request.user and not request.user.is_staff:
            return Response({
                'error': 'Solo el creador puede compartir este reporte'
            }, status=403)
        
        user_ids = request.data.get('user_ids', [])
        report.allowed_users.add(*user_ids)
        
        return Response({
            'status': 'Report shared',
            'shared_with': user_ids
        })

    @action(detail=False, methods=['get'])
    def templates(self, request):
        """Get report templates by type."""
        report_type = request.query_params.get('type', 'sales')
        
        templates = {
            'sales': [
                {
                    'name': 'Pipeline por Etapa',
                    'description': 'Oportunidades agrupadas por etapa de venta',
                    'format_type': 'chart_bar',
                    'columns': ['stage', 'count', 'total_value']
                },
                {
                    'name': 'Pronóstico de Ventas',
                    'description': 'Proyección de ventas para los próximos meses',
                    'format_type': 'chart_line',
                    'columns': ['month', 'expected_revenue', 'opportunities']
                }
            ],
            'marketing': [
                {
                    'name': 'ROI de Campañas',
                    'description': 'Retorno de inversión por campaña',
                    'format_type': 'table',
                    'columns': ['campaign', 'cost', 'revenue', 'roi']
                },
                {
                    'name': 'Conversión de Leads',
                    'description': 'Tasa de conversión de leads por fuente',
                    'format_type': 'chart_pie',
                    'columns': ['source', 'leads', 'converted', 'rate']
                }
            ],
            'support': [
                {
                    'name': 'Tickets por Estado',
                    'description': 'Distribución de tickets por estado',
                    'format_type': 'chart_pie',
                    'columns': ['status', 'count', 'percentage']
                },
                {
                    'name': 'Tiempo de Resolución',
                    'description': 'Tiempo promedio de resolución por prioridad',
                    'format_type': 'chart_bar',
                    'columns': ['priority', 'avg_resolution_hours']
                }
            ]
        }
        
        return Response(templates.get(report_type, []))


class ReportExecutionViewSet(viewsets.ModelViewSet):
    """
    ViewSet for report execution history.
    """
    queryset = ReportExecution.objects.all()
    serializer_class = ReportExecutionSerializer
    permission_classes = [permissions.IsAuthenticated]
    filter_backends = [DjangoFilterBackend, filters.OrderingFilter]
    filterset_fields = ['report', 'executed_by', 'status', 'exported_format']
    ordering_fields = ['executed_at', 'execution_time']
    ordering = ['-executed_at']

    def get_queryset(self):
        queryset = super().get_queryset()
        
        # Users can only see executions of reports they have access to
        if not self.request.user.is_staff:
            queryset = queryset.filter(
                Q(report__is_public=True) |
                Q(report__created_by=self.request.user) |
                Q(report__allowed_users=self.request.user) |
                Q(executed_by=self.request.user)
            ).distinct()
        
        return queryset

    @action(detail=True, methods=['post'])
    def export(self, request, pk=None):
        """Export execution results."""
        execution = self.get_object()
        export_format = request.data.get('format', 'csv')
        
        if export_format not in ['csv', 'excel', 'pdf']:
            return Response({'error': 'Formato no válido'}, status=400)
        
        # In a real implementation, this would generate the actual file
        # For now, we'll just update the execution record
        execution.exported_format = export_format
        execution.save()
        
        return Response({
            'status': 'Export generated',
            'format': export_format,
            'download_url': f'/api/reports/executions/{execution.id}/download/'
        })


class DashboardViewSet(viewsets.ModelViewSet):
    """
    ViewSet for managing dashboards.
    """
    queryset = Dashboard.objects.all()
    serializer_class = DashboardSerializer
    permission_classes = [permissions.IsAuthenticated]
    filter_backends = [DjangoFilterBackend, filters.SearchFilter, filters.OrderingFilter]
    filterset_fields = ['is_default', 'is_public']
    search_fields = ['name', 'description']
    ordering_fields = ['created_at']
    ordering = ['-created_at']

    def get_queryset(self):
        queryset = super().get_queryset()
        
        # Filter dashboards user has access to
        if not self.request.user.is_staff:
            queryset = queryset.filter(
                Q(is_public=True) |
                Q(created_by=self.request.user) |
                Q(allowed_users=self.request.user)
            ).distinct()
        
        return queryset

    def perform_create(self, serializer):
        serializer.save(created_by=self.request.user)

    @action(detail=True, methods=['post'])
    def set_default(self, request, pk=None):
        """Set dashboard as default for current user."""
        dashboard = self.get_object()
        
        # Remove default from other dashboards for this user
        Dashboard.objects.filter(
            created_by=request.user,
            is_default=True
        ).update(is_default=False)
        
        dashboard.is_default = True
        dashboard.save()
        
        return Response({'status': 'Dashboard set as default'})

    @action(detail=True, methods=['post'])
    def duplicate(self, request, pk=None):
        """Duplicate a dashboard."""
        original = self.get_object()
        
        # Create new dashboard
        new_dashboard = Dashboard.objects.create(
            name=f"{original.name} (Copy)",
            description=original.description,
            layout=original.layout,
            refresh_interval=original.refresh_interval,
            created_by=request.user
        )
        
        # Copy widgets
        for widget in original.widgets.all():
            DashboardWidget.objects.create(
                dashboard=new_dashboard,
                report=widget.report,
                widget_type=widget.widget_type,
                title=widget.title,
                position_x=widget.position_x,
                position_y=widget.position_y,
                width=widget.width,
                height=widget.height,
                config=widget.config
            )
        
        serializer = self.get_serializer(new_dashboard)
        return Response(serializer.data, status=status.HTTP_201_CREATED)

    @action(detail=True, methods=['get'])
    def refresh(self, request, pk=None):
        """Refresh all widgets in dashboard."""
        dashboard = self.get_object()
        
        # Refresh each widget
        for widget in dashboard.widgets.all():
            if widget.report:
                # Execute report with cached parameters
                # In a real implementation, this would execute reports
                widget.cached_data = {
                    'last_updated': timezone.now().isoformat(),
                    'data': []  # Simulated data
                }
                widget.cache_expires = timezone.now() + timedelta(
                    seconds=dashboard.refresh_interval
                )
                widget.save()
        
        return Response({'status': 'Dashboard refreshed'})


class DashboardWidgetViewSet(viewsets.ModelViewSet):
    """
    ViewSet for managing dashboard widgets.
    """
    queryset = DashboardWidget.objects.all()
    serializer_class = DashboardWidgetSerializer
    permission_classes = [permissions.IsAuthenticated]
    filter_backends = [DjangoFilterBackend, filters.OrderingFilter]
    filterset_fields = ['dashboard', 'widget_type']
    ordering_fields = ['position_y', 'position_x']
    ordering = ['position_y', 'position_x']

    def get_queryset(self):
        queryset = super().get_queryset()
        
        # Filter widgets from dashboards user has access to
        if not self.request.user.is_staff:
            queryset = queryset.filter(
                Q(dashboard__is_public=True) |
                Q(dashboard__created_by=self.request.user) |
                Q(dashboard__allowed_users=self.request.user)
            ).distinct()
        
        return queryset

    @action(detail=True, methods=['post'])
    def resize(self, request, pk=None):
        """Resize a widget."""
        widget = self.get_object()
        width = request.data.get('width', widget.width)
        height = request.data.get('height', widget.height)
        
        widget.width = width
        widget.height = height
        widget.save()
        
        return Response({'status': 'Widget resized'})

    @action(detail=True, methods=['post'])
    def move(self, request, pk=None):
        """Move a widget to new position."""
        widget = self.get_object()
        position_x = request.data.get('position_x', widget.position_x)
        position_y = request.data.get('position_y', widget.position_y)
        
        widget.position_x = position_x
        widget.position_y = position_y
        widget.save()
        
        return Response({'status': 'Widget moved'})


class SalesReportViewSet(viewsets.ModelViewSet):
    """
    ViewSet for predefined sales reports.
    """
    queryset = SalesReport.objects.all()
    serializer_class = SalesReportSerializer
    permission_classes = [permissions.IsAuthenticated]
    filter_backends = [DjangoFilterBackend, filters.OrderingFilter]
    filterset_fields = ['period_type', 'top_performer']
    ordering_fields = ['generated_at', 'start_date']
    ordering = ['-generated_at']

    @action(detail=False, methods=['post'])
    def generate(self, request):
        """Generate a new sales report."""
        period_type = request.data.get('period_type', 'monthly')
        start_date = request.data.get('start_date')
        end_date = request.data.get('end_date')
        
        if not start_date or not end_date:
            return Response({
                'error': 'Start date and end date are required'
            }, status=400)
        
        # Check if report already exists
        existing = SalesReport.objects.filter(
            period_type=period_type,
            start_date=start_date,
            end_date=end_date
        ).first()
        
        if existing:
            serializer = self.get_serializer(existing)
            return Response(serializer.data)
        
        # Generate new report (simplified calculation)
        from apps.opportunities.models import Opportunity
        
        opportunities = Opportunity.objects.filter(
            created_at__date__gte=start_date,
            created_at__date__lte=end_date
        )
        
        won_opps = opportunities.filter(stage='closed_won')
        lost_opps = opportunities.filter(stage='closed_lost')
        
        # Calculate metrics
        total_revenue = opportunities.aggregate(
            total=Sum('amount')
        )['total'] or 0
        
        won_revenue = won_opps.aggregate(
            total=Sum('amount')
        )['total'] or 0
        
        # Find top performer
        top_performer_data = won_opps.values(
            'assigned_to'
        ).annotate(
            revenue=Sum('amount')
        ).order_by('-revenue').first()
        
        report = SalesReport.objects.create(
            period_type=period_type,
            start_date=start_date,
            end_date=end_date,
            total_opportunities=opportunities.count(),
            total_revenue=total_revenue,
            won_opportunities=won_opps.count(),
            won_revenue=won_revenue,
            lost_opportunities=lost_opps.count(),
            lost_revenue=opportunities.filter(
                stage='closed_lost'
            ).aggregate(total=Sum('amount'))['total'] or 0,
            win_rate=(won_opps.count() / opportunities.count() * 100) if opportunities.count() > 0 else 0,
            avg_deal_size=won_revenue / won_opps.count() if won_opps.count() > 0 else 0,
            pipeline_value=opportunities.filter(
                stage__in=['proposal', 'negotiation']
            ).aggregate(total=Sum('amount'))['total'] or 0,
            top_performer_id=top_performer_data['assigned_to'] if top_performer_data else None,
            generated_by=request.user
        )
        
        serializer = self.get_serializer(report)
        return Response(serializer.data, status=status.HTTP_201_CREATED)


class MarketingReportViewSet(viewsets.ModelViewSet):
    """
    ViewSet for marketing reports.
    """
    queryset = MarketingReport.objects.all()
    serializer_class = MarketingReportSerializer
    permission_classes = [permissions.IsAuthenticated]
    filter_backends = [DjangoFilterBackend, filters.OrderingFilter]
    filterset_fields = ['campaign']
    ordering_fields = ['generated_at', 'report_date']
    ordering = ['-generated_at']

    @action(detail=False, methods=['post'])
    def generate(self, request):
        """Generate marketing report for a campaign."""
        campaign_id = request.data.get('campaign_id')
        
        if not campaign_id:
            return Response({
                'error': 'Campaign ID is required'
            }, status=400)
        
        # In a real implementation, this would calculate actual metrics
        # For now, we'll create a simplified report
        from apps.marketing.models import Campaign
        
        try:
            campaign = Campaign.objects.get(id=campaign_id)
        except Campaign.DoesNotExist:
            return Response({
                'error': 'Campaign not found'
            }, status=404)
        
        # Create report
        report = MarketingReport.objects.create(
            campaign=campaign,
            total_leads=campaign.actual_leads,
            total_cost=campaign.actual_cost,
            revenue_generated=campaign.opportunities.filter(
                stage='closed_won'
            ).aggregate(total=Sum('amount'))['total'] or 0,
            generated_by=request.user
        )
        
        # Calculate ROI
        if report.total_cost > 0:
            report.roi_percentage = (
                (report.revenue_generated - report.total_cost) / report.total_cost * 100
            )
            report.cost_per_lead = report.total_cost / report.total_leads if report.total_leads > 0 else 0
        
        report.save()
        
        serializer = self.get_serializer(report)
        return Response(serializer.data, status=status.HTTP_201_CREATED)


class SupportReportViewSet(viewsets.ModelViewSet):
    """
    ViewSet for support reports.
    """
    queryset = SupportReport.objects.all()
    serializer_class = SupportReportSerializer
    permission_classes = [permissions.IsAuthenticated]
    filter_backends = [DjangoFilterBackend, filters.OrderingFilter]
    filterset_fields = ['period_type']
    ordering_fields = ['generated_at', 'start_date']
    ordering = ['-generated_at']

    @action(detail=False, methods=['post'])
    def generate(self, request):
        """Generate support report."""
        period_type = request.data.get('period_type', 'monthly')
        start_date = request.data.get('start_date')
        end_date = request.data.get('end_date')
        
        if not start_date or not end_date:
            return Response({
                'error': 'Start date and end date are required'
            }, status=400)
        
        # Check if report already exists
        existing = SupportReport.objects.filter(
            period_type=period_type,
            start_date=start_date,
            end_date=end_date
        ).first()
        
        if existing:
            serializer = self.get_serializer(existing)
            return Response(serializer.data)
        
        # Generate new report
        from apps.tickets.models import Ticket
        
        tickets = Ticket.objects.filter(
            created_at__date__gte=start_date,
            created_at__date__lte=end_date
        )
        
        # Calculate metrics
        resolved_tickets = tickets.filter(status='resolved')
        
        # Average response time
        response_times = []
        for ticket in tickets.filter(first_response_date__isnull=False):
            if ticket.response_time:
                response_times.append(ticket.response_time)
        
        avg_response_time = sum(response_times) / len(response_times) if response_times else 0
        
        # SLA compliance
        total_with_sla = tickets.filter(sla_due_date__isnull=False).count()
        overdue = 0
        for ticket in tickets.filter(sla_due_date__isnull=False):
            if ticket.is_overdue:
                overdue += 1
        
        sla_compliance = ((total_with_sla - overdue) / total_with_sla * 100) if total_with_sla > 0 else 100
        
        # Satisfaction
        rated_tickets = tickets.filter(satisfaction_rating__isnull=False)
        avg_satisfaction = rated_tickets.aggregate(
            avg=Avg('satisfaction_rating')
        )['avg'] or 0
        
        report = SupportReport.objects.create(
            period_type=period_type,
            start_date=start_date,
            end_date=end_date,
            total_tickets=tickets.count(),
            new_tickets=tickets.filter(status='new').count(),
            resolved_tickets=resolved_tickets.count(),
            closed_tickets=tickets.filter(status='closed').count(),
            avg_first_response_time=avg_response_time,
            sla_met_percentage=sla_compliance,
            avg_satisfaction_rating=avg_satisfaction,
            satisfaction_responses=rated_tickets.count(),
            generated_by=request.user
        )
        
        serializer = self.get_serializer(report)
        return Response(serializer.data, status=status.HTTP_201_CREATED)