from rest_framework import viewsets, permissions, filters
from rest_framework.decorators import action
from rest_framework.response import Response
from django_filters.rest_framework import DjangoFilterBackend
from django.db.models import Q, Sum, Avg
from django.utils import timezone
from .models import Opportunity, OpportunityProduct, Quote, Commission
from .serializers import (
    OpportunitySerializer,
    OpportunityProductSerializer,
    QuoteSerializer,
    CommissionSerializer
)


class OpportunityViewSet(viewsets.ModelViewSet):
    """
    ViewSet for managing opportunities.
    Provides CRUD operations and custom actions for sales opportunities.
    """
    queryset = Opportunity.objects.all()
    serializer_class = OpportunitySerializer
    permission_classes = [permissions.IsAuthenticated]
    filter_backends = [DjangoFilterBackend, filters.SearchFilter, filters.OrderingFilter]
    filterset_fields = ['stage', 'probability', 'assigned_to', 'is_active', 'lead_source', 'product_type', 'region']
    search_fields = ['name', 'opportunity_id', 'description', 'account__name', 'contact__first_name', 'contact__last_name']
    ordering_fields = ['created_at', 'close_date', 'amount', 'probability', 'expected_revenue']
    ordering = ['-created_at']

    def get_queryset(self):
        queryset = super().get_queryset()
        # Filter by date range if provided
        start_date = self.request.query_params.get('start_date')
        end_date = self.request.query_params.get('end_date')
        
        if start_date:
            queryset = queryset.filter(created_at__gte=start_date)
        if end_date:
            queryset = queryset.filter(created_at__lte=end_date)
        
        return queryset

    @action(detail=False, methods=['get'])
    def pipeline(self, request):
        """Get opportunity pipeline grouped by stage."""
        pipeline_data = []
        for stage, stage_name in Opportunity.STAGE_CHOICES:
            opportunities = self.get_queryset().filter(stage=stage, is_active=True)
            pipeline_data.append({
                'stage': stage,
                'stage_name': stage_name,
                'count': opportunities.count(),
                'total_value': opportunities.aggregate(total=Sum('amount'))['total'] or 0,
                'expected_revenue': opportunities.aggregate(total=Sum('expected_revenue'))['total'] or 0
            })
        return Response(pipeline_data)

    @action(detail=False, methods=['get'])
    def forecast(self, request):
        """Get sales forecast based on close dates and probabilities."""
        queryset = self.get_queryset().filter(
            is_active=True,
            stage__in=['proposal', 'negotiation']
        )
        
        # Group by month
        forecast_data = []
        for i in range(3):  # Next 3 months
            month_start = timezone.now().date().replace(day=1) + timezone.timedelta(days=i*30)
            month_end = (month_start + timezone.timedelta(days=32)).replace(day=1) - timezone.timedelta(days=1)
            
            month_opportunities = queryset.filter(
                close_date__gte=month_start,
                close_date__lte=month_end
            )
            
            forecast_data.append({
                'month': month_start.strftime('%Y-%m'),
                'opportunities_count': month_opportunities.count(),
                'total_value': month_opportunities.aggregate(total=Sum('amount'))['total'] or 0,
                'expected_revenue': month_opportunities.aggregate(total=Sum('expected_revenue'))['total'] or 0
            })
        
        return Response(forecast_data)

    @action(detail=True, methods=['post'])
    def calculate_commission(self, request, pk=None):
        """Calculate commission for an opportunity."""
        opportunity = self.get_object()
        if opportunity.stage != 'closed_won':
            return Response({'error': 'Solo se puede calcular comisión para oportunidades ganadas'}, status=400)
        
        # Create or update commission
        commission, created = Commission.objects.get_or_create(
            opportunity=opportunity,
            sales_rep=opportunity.assigned_to,
            defaults={'sale_amount': opportunity.amount}
        )
        
        if not created:
            commission.sale_amount = opportunity.amount
        
        commission.calculate_commission()
        commission.save()
        
        serializer = CommissionSerializer(commission)
        return Response(serializer.data)


class OpportunityProductViewSet(viewsets.ModelViewSet):
    """
    ViewSet for managing opportunity products.
    """
    queryset = OpportunityProduct.objects.all()
    serializer_class = OpportunityProductSerializer
    permission_classes = [permissions.IsAuthenticated]
    filter_backends = [DjangoFilterBackend, filters.SearchFilter]
    filterset_fields = ['opportunity', 'product_code']
    search_fields = ['product_name', 'product_code']


class QuoteViewSet(viewsets.ModelViewSet):
    """
    ViewSet for managing quotes.
    """
    queryset = Quote.objects.all()
    serializer_class = QuoteSerializer
    permission_classes = [permissions.IsAuthenticated]
    filter_backends = [DjangoFilterBackend, filters.SearchFilter, filters.OrderingFilter]
    filterset_fields = ['opportunity', 'status']
    search_fields = ['quote_number', 'subject', 'description']
    ordering_fields = ['created_at', 'issue_date', 'expiration_date', 'total']
    ordering = ['-created_at']

    @action(detail=True, methods=['post'])
    def send(self, request, pk=None):
        """Mark quote as sent."""
        quote = self.get_object()
        quote.status = 'sent'
        quote.save()
        return Response({'status': 'Quote marked as sent'})

    @action(detail=True, methods=['post'])
    def accept(self, request, pk=None):
        """Accept a quote."""
        quote = self.get_object()
        quote.status = 'accepted'
        quote.save()
        
        # Update opportunity stage
        quote.opportunity.stage = 'closed_won'
        quote.opportunity.save()
        
        return Response({'status': 'Quote accepted'})

    @action(detail=True, methods=['post'])
    def reject(self, request, pk=None):
        """Reject a quote."""
        quote = self.get_object()
        quote.status = 'rejected'
        quote.save()
        return Response({'status': 'Quote rejected'})


class CommissionViewSet(viewsets.ModelViewSet):
    """
    ViewSet for managing commissions.
    """
    queryset = Commission.objects.all()
    serializer_class = CommissionSerializer
    permission_classes = [permissions.IsAuthenticated]
    filter_backends = [DjangoFilterBackend, filters.SearchFilter, filters.OrderingFilter]
    filterset_fields = ['sales_rep', 'is_paid', 'is_special_campaign']
    search_fields = ['opportunity__name', 'opportunity__opportunity_id', 'sales_rep__username']
    ordering_fields = ['created_at', 'commission_amount', 'sale_amount']
    ordering = ['-created_at']

    def get_queryset(self):
        queryset = super().get_queryset()
        # Users can only see their own commissions unless they're managers
        if not self.request.user.is_staff:
            queryset = queryset.filter(sales_rep=self.request.user)
        return queryset

    @action(detail=False, methods=['get'])
    def summary(self, request):
        """Get commission summary for the current user."""
        user = request.user
        if request.user.is_staff and request.query_params.get('user_id'):
            user_id = request.query_params.get('user_id')
            commissions = Commission.objects.filter(sales_rep_id=user_id)
        else:
            commissions = Commission.objects.filter(sales_rep=user)
        
        summary = {
            'total_commissions': commissions.count(),
            'total_amount': commissions.aggregate(total=Sum('commission_amount'))['total'] or 0,
            'paid_amount': commissions.filter(is_paid=True).aggregate(total=Sum('commission_amount'))['total'] or 0,
            'pending_amount': commissions.filter(is_paid=False).aggregate(total=Sum('commission_amount'))['total'] or 0,
            'average_commission_rate': commissions.aggregate(avg=Avg('commission_rate'))['avg'] or 0
        }
        
        return Response(summary)

    @action(detail=True, methods=['post'])
    def mark_paid(self, request, pk=None):
        """Mark commission as paid."""
        if not request.user.is_staff:
            return Response({'error': 'Solo el personal autorizado puede marcar comisiones como pagadas'}, status=403)
        
        commission = self.get_object()
        commission.is_paid = True
        commission.paid_date = timezone.now().date()
        commission.save()
        
        return Response({'status': 'Commission marked as paid'})