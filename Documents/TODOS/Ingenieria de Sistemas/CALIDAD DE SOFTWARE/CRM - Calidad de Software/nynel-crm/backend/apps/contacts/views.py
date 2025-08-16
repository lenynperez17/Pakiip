from rest_framework import viewsets, filters, status
from rest_framework.decorators import action
from rest_framework.response import Response
from rest_framework.permissions import IsAuthenticated
from django_filters.rest_framework import DjangoFilterBackend
from django.db.models import Q, Count
from django.utils import timezone

from .models import Account, Contact, Activity
from .serializers import (
    AccountSerializer, ContactSerializer, ActivitySerializer,
    ContactListSerializer, AccountListSerializer
)


class AccountViewSet(viewsets.ModelViewSet):
    """API endpoint for managing accounts."""
    queryset = Account.objects.all()
    permission_classes = [IsAuthenticated]
    filter_backends = [DjangoFilterBackend, filters.SearchFilter, filters.OrderingFilter]
    filterset_fields = ['account_type', 'industry', 'billing_city', 'assigned_to']
    search_fields = ['name', 'ruc', 'billing_city', 'website']
    ordering_fields = ['name', 'created_at', 'updated_at']
    ordering = ['-created_at']
    
    def get_serializer_class(self):
        if self.action == 'list':
            return AccountListSerializer
        return AccountSerializer
    
    def perform_create(self, serializer):
        serializer.save(created_by=self.request.user)
    
    @action(detail=True, methods=['get'])
    def contacts(self, request, pk=None):
        """Get contacts for a specific account."""
        account = self.get_object()
        contacts = Contact.objects.filter(account=account)
        serializer = ContactListSerializer(contacts, many=True)
        return Response(serializer.data)
    
    @action(detail=True, methods=['get'])
    def activities(self, request, pk=None):
        """Get activities for a specific account."""
        account = self.get_object()
        activities = Activity.objects.filter(account=account)
        serializer = ActivitySerializer(activities, many=True)
        return Response(serializer.data)
    
    @action(detail=False, methods=['get'])
    def statistics(self, request):
        """Get account statistics."""
        queryset = self.filter_queryset(self.get_queryset())
        stats = {
            'total_accounts': queryset.count(),
            'by_type': queryset.values('account_type').annotate(count=Count('id')),
            'recent_accounts': queryset.filter(
                created_at__gte=timezone.now() - timezone.timedelta(days=30)
            ).count(),
        }
        return Response(stats)


class ContactViewSet(viewsets.ModelViewSet):
    """API endpoint for managing contacts."""
    queryset = Contact.objects.select_related('account', 'assigned_to', 'created_by').all()
    permission_classes = [IsAuthenticated]
    filter_backends = [DjangoFilterBackend, filters.SearchFilter, filters.OrderingFilter]
    filterset_fields = ['account', 'job_title', 'department', 'assigned_to', 'preferred_contact_method']
    search_fields = ['first_name', 'last_name', 'email', 'phone', 'job_title', 'account__name']
    ordering_fields = ['first_name', 'last_name', 'created_at', 'last_activity_date']
    ordering = ['-created_at']
    
    def get_serializer_class(self):
        if self.action == 'list':
            return ContactListSerializer
        return ContactSerializer
    
    def perform_create(self, serializer):
        serializer.save(created_by=self.request.user)
    
    @action(detail=True, methods=['get'])
    def activities(self, request, pk=None):
        """Get activities for a specific contact."""
        contact = self.get_object()
        activities = Activity.objects.filter(contact=contact).order_by('-due_date')
        serializer = ActivitySerializer(activities, many=True)
        return Response(serializer.data)
    
    @action(detail=False, methods=['get'])
    def search_by_email(self, request):
        """Search contacts by email."""
        email = request.query_params.get('email', '')
        if not email:
            return Response({'error': 'Email parameter is required'}, 
                          status=status.HTTP_400_BAD_REQUEST)
        
        contacts = Contact.objects.filter(email__icontains=email)
        serializer = ContactListSerializer(contacts, many=True)
        return Response(serializer.data)
    
    @action(detail=False, methods=['get'])
    def no_recent_activity(self, request):
        """Get contacts with no recent activity."""
        days = int(request.query_params.get('days', 30))
        cutoff_date = timezone.now() - timezone.timedelta(days=days)
        
        contacts = Contact.objects.filter(
            Q(last_activity_date__lt=cutoff_date) | Q(last_activity_date__isnull=True)
        )
        serializer = ContactListSerializer(contacts, many=True)
        return Response(serializer.data)
    
    @action(detail=False, methods=['get'])
    def statistics(self, request):
        """Get contact statistics."""
        queryset = self.filter_queryset(self.get_queryset())
        stats = {
            'total_contacts': queryset.count(),
            'with_accounts': queryset.filter(account__isnull=False).count(),
            'without_accounts': queryset.filter(account__isnull=True).count(),
            'recent_contacts': queryset.filter(
                created_at__gte=timezone.now() - timezone.timedelta(days=30)
            ).count(),
            'by_preferred_method': queryset.values('preferred_contact_method').annotate(
                count=Count('id')
            ),
        }
        return Response(stats)


class ActivityViewSet(viewsets.ModelViewSet):
    """API endpoint for managing activities."""
    queryset = Activity.objects.select_related(
        'contact', 'account', 'assigned_to', 'created_by'
    ).all()
    permission_classes = [IsAuthenticated]
    filter_backends = [DjangoFilterBackend, filters.SearchFilter, filters.OrderingFilter]
    filterset_fields = ['activity_type', 'status', 'assigned_to', 'contact', 'account']
    search_fields = ['subject', 'description', 'contact__first_name', 'contact__last_name']
    ordering_fields = ['due_date', 'created_at', 'completed_date']
    ordering = ['-due_date']
    serializer_class = ActivitySerializer
    
    def perform_create(self, serializer):
        serializer.save(created_by=self.request.user)
    
    def get_queryset(self):
        """Filter activities by user if not admin."""
        queryset = super().get_queryset()
        if not self.request.user.is_staff:
            queryset = queryset.filter(assigned_to=self.request.user)
        return queryset
    
    @action(detail=False, methods=['get'])
    def overdue(self, request):
        """Get overdue activities."""
        now = timezone.now()
        activities = self.get_queryset().filter(
            status__in=['planned', 'pending'],
            due_date__lt=now
        )
        serializer = self.get_serializer(activities, many=True)
        return Response(serializer.data)
    
    @action(detail=False, methods=['get'])
    def today(self, request):
        """Get today's activities."""
        today = timezone.now().date()
        activities = self.get_queryset().filter(
            due_date__date=today
        )
        serializer = self.get_serializer(activities, many=True)
        return Response(serializer.data)
    
    @action(detail=False, methods=['get'])
    def upcoming(self, request):
        """Get upcoming activities (next 7 days)."""
        start_date = timezone.now()
        end_date = start_date + timezone.timedelta(days=7)
        
        activities = self.get_queryset().filter(
            status='planned',
            due_date__range=[start_date, end_date]
        )
        serializer = self.get_serializer(activities, many=True)
        return Response(serializer.data)
    
    @action(detail=True, methods=['post'])
    def mark_complete(self, request, pk=None):
        """Mark activity as completed."""
        activity = self.get_object()
        activity.status = 'completed'
        activity.completed_date = timezone.now()
        activity.save()
        
        serializer = self.get_serializer(activity)
        return Response(serializer.data)
    
    @action(detail=False, methods=['get'])
    def statistics(self, request):
        """Get activity statistics."""
        queryset = self.filter_queryset(self.get_queryset())
        now = timezone.now()
        
        stats = {
            'total_activities': queryset.count(),
            'completed': queryset.filter(status='completed').count(),
            'overdue': queryset.filter(
                status__in=['planned', 'pending'],
                due_date__lt=now
            ).count(),
            'today': queryset.filter(due_date__date=now.date()).count(),
            'by_type': queryset.values('activity_type').annotate(count=Count('id')),
            'by_status': queryset.values('status').annotate(count=Count('id')),
        }
        return Response(stats)