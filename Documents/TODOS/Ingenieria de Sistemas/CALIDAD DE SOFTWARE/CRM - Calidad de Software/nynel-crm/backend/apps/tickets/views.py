from rest_framework import viewsets, permissions, filters, status
from rest_framework.decorators import action
from rest_framework.response import Response
from django_filters.rest_framework import DjangoFilterBackend
from django.db.models import Count, Q, Avg, F
from django.utils import timezone
from datetime import timedelta
from .models import Ticket, TicketComment, KnowledgeBase, TicketTemplate
from .serializers import (
    TicketSerializer, TicketCommentSerializer,
    KnowledgeBaseSerializer, TicketTemplateSerializer
)


class TicketViewSet(viewsets.ModelViewSet):
    """
    ViewSet for managing support tickets.
    """
    queryset = Ticket.objects.all()
    serializer_class = TicketSerializer
    permission_classes = [permissions.IsAuthenticated]
    filter_backends = [DjangoFilterBackend, filters.SearchFilter, filters.OrderingFilter]
    filterset_fields = ['priority', 'status', 'ticket_type', 'assigned_to', 'assigned_team']
    search_fields = ['ticket_number', 'subject', 'description', 'contact__first_name', 'contact__last_name', 'account__name']
    ordering_fields = ['created_at', 'priority', 'sla_due_date', 'updated_at']
    ordering = ['-created_at']

    def get_queryset(self):
        queryset = super().get_queryset()
        
        # Filter overdue tickets
        if self.request.query_params.get('overdue_only'):
            queryset = queryset.filter(
                sla_due_date__lt=timezone.now(),
                status__in=['new', 'open', 'pending', 'on_hold']
            )
        
        # Filter by user's tickets
        if self.request.query_params.get('my_tickets'):
            queryset = queryset.filter(assigned_to=self.request.user)
        
        # Filter by date range
        start_date = self.request.query_params.get('start_date')
        end_date = self.request.query_params.get('end_date')
        
        if start_date:
            queryset = queryset.filter(created_at__gte=start_date)
        if end_date:
            queryset = queryset.filter(created_at__lte=end_date)
        
        return queryset

    def perform_create(self, serializer):
        serializer.save(created_by=self.request.user)

    @action(detail=True, methods=['post'])
    def assign(self, request, pk=None):
        """Assign ticket to a user or team."""
        ticket = self.get_object()
        user_id = request.data.get('user_id')
        team = request.data.get('team')
        
        if user_id:
            ticket.assigned_to_id = user_id
        if team:
            ticket.assigned_team = team
        
        ticket.status = 'open' if ticket.status == 'new' else ticket.status
        ticket.save()
        
        return Response({'status': 'Ticket assigned'})

    @action(detail=True, methods=['post'])
    def change_status(self, request, pk=None):
        """Change ticket status."""
        ticket = self.get_object()
        new_status = request.data.get('status')
        
        if new_status not in dict(Ticket.STATUS_CHOICES):
            return Response({'error': 'Invalid status'}, status=400)
        
        ticket.status = new_status
        ticket.save()
        
        return Response({'status': f'Ticket status changed to {new_status}'})

    @action(detail=True, methods=['post'])
    def resolve(self, request, pk=None):
        """Resolve a ticket."""
        ticket = self.get_object()
        resolution_notes = request.data.get('resolution_notes', '')
        
        ticket.status = 'resolved'
        ticket.resolution_date = timezone.now()
        if resolution_notes:
            ticket.internal_notes += f"\n\nResolution: {resolution_notes}"
        ticket.save()
        
        return Response({'status': 'Ticket resolved'})

    @action(detail=True, methods=['post'])
    def close(self, request, pk=None):
        """Close a ticket."""
        ticket = self.get_object()
        ticket.status = 'closed'
        ticket.closed_date = timezone.now()
        ticket.save()
        
        return Response({'status': 'Ticket closed'})

    @action(detail=True, methods=['post'])
    def add_satisfaction(self, request, pk=None):
        """Add customer satisfaction rating."""
        ticket = self.get_object()
        rating = request.data.get('rating')
        comment = request.data.get('comment', '')
        
        if not rating or rating not in range(1, 6):
            return Response({'error': 'Rating must be between 1 and 5'}, status=400)
        
        ticket.satisfaction_rating = rating
        ticket.satisfaction_comment = comment
        ticket.save()
        
        return Response({'status': 'Satisfaction rating added'})

    @action(detail=False, methods=['get'])
    def statistics(self, request):
        """Get ticket statistics."""
        queryset = self.get_queryset()
        
        # Calculate statistics
        total_tickets = queryset.count()
        
        # Status distribution
        status_distribution = {}
        for status, label in Ticket.STATUS_CHOICES:
            status_distribution[status] = queryset.filter(status=status).count()
        
        # Priority distribution
        priority_distribution = {}
        for priority, label in Ticket.PRIORITY_CHOICES:
            priority_distribution[priority] = queryset.filter(priority=priority).count()
        
        # Average resolution time
        resolved_tickets = queryset.filter(status='resolved', resolution_date__isnull=False)
        avg_resolution_time = None
        if resolved_tickets.exists():
            resolution_times = []
            for ticket in resolved_tickets:
                if ticket.resolution_time:
                    resolution_times.append(ticket.resolution_time)
            if resolution_times:
                avg_resolution_time = sum(resolution_times) / len(resolution_times)
        
        # Average satisfaction
        rated_tickets = queryset.filter(satisfaction_rating__isnull=False)
        avg_satisfaction = rated_tickets.aggregate(avg=Avg('satisfaction_rating'))['avg'] or 0
        
        # SLA performance
        total_with_sla = queryset.filter(sla_due_date__isnull=False).count()
        overdue_count = queryset.filter(
            sla_due_date__lt=timezone.now(),
            status__in=['new', 'open', 'pending', 'on_hold']
        ).count()
        sla_compliance_rate = ((total_with_sla - overdue_count) / total_with_sla * 100) if total_with_sla > 0 else 100
        
        return Response({
            'total_tickets': total_tickets,
            'status_distribution': status_distribution,
            'priority_distribution': priority_distribution,
            'avg_resolution_time_hours': avg_resolution_time,
            'avg_satisfaction_rating': round(avg_satisfaction, 2),
            'sla_compliance_rate': round(sla_compliance_rate, 2),
            'overdue_tickets': overdue_count
        })

    @action(detail=False, methods=['get'])
    def workload(self, request):
        """Get workload distribution among agents."""
        active_tickets = self.get_queryset().filter(
            status__in=['open', 'pending', 'on_hold']
        )
        
        workload = active_tickets.values('assigned_to__username', 'assigned_to__first_name', 'assigned_to__last_name').annotate(
            ticket_count=Count('id'),
            urgent_count=Count('id', filter=Q(priority='urgent')),
            high_count=Count('id', filter=Q(priority='high')),
            overdue_count=Count('id', filter=Q(sla_due_date__lt=timezone.now()))
        ).order_by('-ticket_count')
        
        return Response(list(workload))


class TicketCommentViewSet(viewsets.ModelViewSet):
    """
    ViewSet for managing ticket comments.
    """
    queryset = TicketComment.objects.all()
    serializer_class = TicketCommentSerializer
    permission_classes = [permissions.IsAuthenticated]
    filter_backends = [DjangoFilterBackend, filters.OrderingFilter]
    filterset_fields = ['ticket', 'is_internal', 'author']
    ordering_fields = ['created_at']
    ordering = ['created_at']

    def get_queryset(self):
        queryset = super().get_queryset()
        
        # Filter by ticket if provided
        ticket_id = self.request.query_params.get('ticket_id')
        if ticket_id:
            queryset = queryset.filter(ticket_id=ticket_id)
        
        # Non-staff users shouldn't see internal notes
        if not self.request.user.is_staff:
            queryset = queryset.filter(is_internal=False)
        
        return queryset

    def perform_create(self, serializer):
        serializer.save(
            author=self.request.user,
            author_name=self.request.user.get_full_name() or self.request.user.username,
            author_email=self.request.user.email
        )


class KnowledgeBaseViewSet(viewsets.ModelViewSet):
    """
    ViewSet for managing knowledge base articles.
    """
    queryset = KnowledgeBase.objects.all()
    serializer_class = KnowledgeBaseSerializer
    permission_classes = [permissions.IsAuthenticated]
    filter_backends = [DjangoFilterBackend, filters.SearchFilter, filters.OrderingFilter]
    filterset_fields = ['category', 'is_published', 'is_featured']
    search_fields = ['title', 'content', 'tags', 'summary']
    ordering_fields = ['created_at', 'views', 'helpful_votes', 'published_date']
    ordering = ['-created_at']

    def get_queryset(self):
        queryset = super().get_queryset()
        
        # Non-staff users can only see published articles
        if not self.request.user.is_staff:
            queryset = queryset.filter(is_published=True)
        
        # Filter by tags
        tags = self.request.query_params.get('tags')
        if tags:
            tags_list = tags.split(',')
            for tag in tags_list:
                queryset = queryset.filter(tags__icontains=tag.strip())
        
        return queryset

    def perform_create(self, serializer):
        serializer.save(author=self.request.user)

    @action(detail=True, methods=['post'])
    def publish(self, request, pk=None):
        """Publish an article."""
        if not request.user.is_staff:
            return Response({'error': 'Only staff can publish articles'}, status=403)
        
        article = self.get_object()
        article.is_published = True
        article.published_date = timezone.now()
        article.save()
        
        return Response({'status': 'Article published'})

    @action(detail=True, methods=['post'])
    def unpublish(self, request, pk=None):
        """Unpublish an article."""
        if not request.user.is_staff:
            return Response({'error': 'Only staff can unpublish articles'}, status=403)
        
        article = self.get_object()
        article.is_published = False
        article.save()
        
        return Response({'status': 'Article unpublished'})

    @action(detail=True, methods=['post'])
    def vote(self, request, pk=None):
        """Vote on article helpfulness."""
        article = self.get_object()
        is_helpful = request.data.get('is_helpful', True)
        
        if is_helpful:
            article.helpful_votes += 1
        else:
            article.not_helpful_votes += 1
        
        article.save()
        
        return Response({
            'status': 'Vote recorded',
            'helpfulness_score': article.helpfulness_score
        })

    @action(detail=True, methods=['get'])
    def view(self, request, pk=None):
        """View article and increment view count."""
        article = self.get_object()
        article.increment_views()
        
        serializer = self.get_serializer(article)
        return Response(serializer.data)

    @action(detail=False, methods=['get'])
    def popular(self, request):
        """Get most popular articles."""
        limit = int(request.query_params.get('limit', 10))
        articles = self.get_queryset().filter(
            is_published=True
        ).order_by('-views')[:limit]
        
        serializer = self.get_serializer(articles, many=True)
        return Response(serializer.data)

    @action(detail=False, methods=['get'])
    def featured(self, request):
        """Get featured articles."""
        articles = self.get_queryset().filter(
            is_published=True,
            is_featured=True
        ).order_by('-published_date')
        
        serializer = self.get_serializer(articles, many=True)
        return Response(serializer.data)


class TicketTemplateViewSet(viewsets.ModelViewSet):
    """
    ViewSet for managing ticket templates.
    """
    queryset = TicketTemplate.objects.filter(is_active=True)
    serializer_class = TicketTemplateSerializer
    permission_classes = [permissions.IsAuthenticated]
    filter_backends = [filters.SearchFilter, filters.OrderingFilter]
    search_fields = ['name', 'category', 'subject', 'content']
    ordering_fields = ['category', 'name', 'times_used']
    ordering = ['category', 'name']

    def perform_create(self, serializer):
        serializer.save(created_by=self.request.user)

    @action(detail=True, methods=['post'])
    def use(self, request, pk=None):
        """Use a template and increment usage count."""
        template = self.get_object()
        template.times_used += 1
        template.save()
        
        # Replace variables if provided
        variables = request.data.get('variables', {})
        content = template.content
        subject = template.subject
        
        for key, value in variables.items():
            content = content.replace(f'{{{{{key}}}}}', str(value))
            subject = subject.replace(f'{{{{{key}}}}}', str(value))
        
        return Response({
            'subject': subject,
            'content': content
        })

    @action(detail=False, methods=['get'])
    def categories(self, request):
        """Get list of template categories."""
        categories = self.get_queryset().values_list(
            'category', flat=True
        ).distinct().order_by('category')
        
        return Response(list(filter(None, categories)))