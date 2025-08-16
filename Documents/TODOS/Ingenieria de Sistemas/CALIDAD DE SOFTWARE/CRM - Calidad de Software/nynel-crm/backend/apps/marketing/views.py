from rest_framework import viewsets, permissions, filters, status
from rest_framework.decorators import action
from rest_framework.response import Response
from django_filters.rest_framework import DjangoFilterBackend
from django.db.models import Count, Q, Sum, Avg
from django.utils import timezone
from .models import (
    Campaign, EmailTemplate, MailingList, MailingListMember,
    EmailCampaign, EmailTracking, Lead
)
from .serializers import (
    CampaignSerializer, EmailTemplateSerializer, MailingListSerializer,
    MailingListMemberSerializer, EmailCampaignSerializer,
    EmailTrackingSerializer, LeadSerializer
)


class CampaignViewSet(viewsets.ModelViewSet):
    """
    ViewSet for managing marketing campaigns.
    """
    queryset = Campaign.objects.all()
    serializer_class = CampaignSerializer
    permission_classes = [permissions.IsAuthenticated]
    filter_backends = [DjangoFilterBackend, filters.SearchFilter, filters.OrderingFilter]
    filterset_fields = ['campaign_type', 'status', 'assigned_to']
    search_fields = ['name', 'description', 'objective']
    ordering_fields = ['start_date', 'end_date', 'created_at', 'budget']
    ordering = ['-start_date']

    def get_queryset(self):
        queryset = super().get_queryset()
        # Filter active campaigns
        if self.request.query_params.get('active_only'):
            queryset = queryset.filter(
                status='active',
                start_date__lte=timezone.now().date(),
                end_date__gte=timezone.now().date()
            )
        return queryset

    @action(detail=True, methods=['post'])
    def activate(self, request, pk=None):
        """Activate a campaign."""
        campaign = self.get_object()
        campaign.status = 'active'
        campaign.save()
        return Response({'status': 'Campaign activated'})

    @action(detail=True, methods=['post'])
    def pause(self, request, pk=None):
        """Pause a campaign."""
        campaign = self.get_object()
        campaign.status = 'paused'
        campaign.save()
        return Response({'status': 'Campaign paused'})

    @action(detail=True, methods=['post'])
    def complete(self, request, pk=None):
        """Mark campaign as completed."""
        campaign = self.get_object()
        campaign.status = 'completed'
        campaign.save()
        return Response({'status': 'Campaign completed'})

    @action(detail=True, methods=['get'])
    def metrics(self, request, pk=None):
        """Get campaign metrics."""
        campaign = self.get_object()
        
        # Calculate metrics
        leads = campaign.leads.all()
        opportunities = campaign.opportunities.all()
        email_campaigns = campaign.email_campaigns.all()
        
        metrics = {
            'campaign_id': campaign.id,
            'campaign_name': campaign.name,
            'status': campaign.status,
            'budget': float(campaign.budget),
            'actual_cost': float(campaign.actual_cost),
            'roi': campaign.roi,
            'leads': {
                'expected': campaign.expected_leads,
                'actual': campaign.actual_leads,
                'conversion_rate': (leads.filter(is_converted=True).count() / leads.count() * 100) if leads.count() > 0 else 0
            },
            'opportunities': {
                'total': opportunities.count(),
                'won': opportunities.filter(stage='closed_won').count(),
                'total_value': float(opportunities.aggregate(total=Sum('amount'))['total'] or 0)
            },
            'email_metrics': {
                'campaigns_count': email_campaigns.count(),
                'total_sent': email_campaigns.aggregate(total=Sum('sent_count'))['total'] or 0,
                'avg_open_rate': email_campaigns.aggregate(avg=Avg('opened_count'))['avg'] or 0,
                'avg_click_rate': email_campaigns.aggregate(avg=Avg('clicked_count'))['avg'] or 0
            }
        }
        
        return Response(metrics)


class EmailTemplateViewSet(viewsets.ModelViewSet):
    """
    ViewSet for managing email templates.
    """
    queryset = EmailTemplate.objects.filter(is_active=True)
    serializer_class = EmailTemplateSerializer
    permission_classes = [permissions.IsAuthenticated]
    filter_backends = [filters.SearchFilter, filters.OrderingFilter]
    search_fields = ['name', 'subject']
    ordering_fields = ['created_at', 'times_used']
    ordering = ['-created_at']

    @action(detail=True, methods=['post'])
    def preview(self, request, pk=None):
        """Preview email template with sample data."""
        template = self.get_object()
        sample_data = request.data.get('sample_data', {
            'first_name': 'Juan',
            'last_name': 'Pérez',
            'company': 'Empresa Demo',
            'unsubscribe_link': '#'
        })
        
        # Replace variables in template
        subject = template.subject
        body_html = template.body_html
        
        for key, value in sample_data.items():
            subject = subject.replace(f'{{{{{key}}}}}', str(value))
            body_html = body_html.replace(f'{{{{{key}}}}}', str(value))
        
        return Response({
            'subject': subject,
            'body_html': body_html
        })


class MailingListViewSet(viewsets.ModelViewSet):
    """
    ViewSet for managing mailing lists.
    """
    queryset = MailingList.objects.all()
    serializer_class = MailingListSerializer
    permission_classes = [permissions.IsAuthenticated]
    filter_backends = [DjangoFilterBackend, filters.SearchFilter, filters.OrderingFilter]
    filterset_fields = ['is_dynamic']
    search_fields = ['name', 'description']
    ordering_fields = ['created_at', 'member_count']
    ordering = ['-created_at']

    @action(detail=True, methods=['post'])
    def update_members(self, request, pk=None):
        """Update mailing list members based on criteria (for dynamic lists)."""
        mailing_list = self.get_object()
        
        if not mailing_list.is_dynamic:
            return Response({'error': 'Esta no es una lista dinámica'}, status=400)
        
        # Update member count
        mailing_list.update_member_count()
        
        return Response({
            'status': 'Members updated',
            'member_count': mailing_list.member_count
        })

    @action(detail=True, methods=['post'])
    def import_contacts(self, request, pk=None):
        """Import contacts to mailing list."""
        mailing_list = self.get_object()
        contact_ids = request.data.get('contact_ids', [])
        import_source = request.data.get('import_source', 'manual')
        
        added_count = 0
        for contact_id in contact_ids:
            member, created = MailingListMember.objects.get_or_create(
                mailing_list=mailing_list,
                contact_id=contact_id,
                defaults={
                    'import_source': import_source,
                    'added_by': request.user
                }
            )
            if created:
                added_count += 1
        
        mailing_list.update_member_count()
        
        return Response({
            'status': 'Contacts imported',
            'added_count': added_count,
            'total_members': mailing_list.member_count
        })


class MailingListMemberViewSet(viewsets.ModelViewSet):
    """
    ViewSet for managing mailing list members.
    """
    queryset = MailingListMember.objects.all()
    serializer_class = MailingListMemberSerializer
    permission_classes = [permissions.IsAuthenticated]
    filter_backends = [DjangoFilterBackend, filters.OrderingFilter]
    filterset_fields = ['mailing_list', 'is_active', 'is_unsubscribed']
    ordering_fields = ['added_date']
    ordering = ['-added_date']

    @action(detail=True, methods=['post'])
    def unsubscribe(self, request, pk=None):
        """Unsubscribe a member from mailing list."""
        member = self.get_object()
        reason = request.data.get('reason', '')
        member.unsubscribe(reason)
        
        # Update mailing list count
        member.mailing_list.update_member_count()
        
        return Response({'status': 'Member unsubscribed'})


class EmailCampaignViewSet(viewsets.ModelViewSet):
    """
    ViewSet for managing email campaigns.
    """
    queryset = EmailCampaign.objects.all()
    serializer_class = EmailCampaignSerializer
    permission_classes = [permissions.IsAuthenticated]
    filter_backends = [DjangoFilterBackend, filters.OrderingFilter]
    filterset_fields = ['campaign', 'is_sent', 'is_test']
    ordering_fields = ['scheduled_date', 'sent_date', 'created_at']
    ordering = ['-scheduled_date']

    @action(detail=True, methods=['post'])
    def send_test(self, request, pk=None):
        """Send test email."""
        email_campaign = self.get_object()
        test_email = request.data.get('test_email')
        
        if not test_email:
            return Response({'error': 'Email de prueba requerido'}, status=400)
        
        # In a real implementation, this would send an actual email
        # For now, we'll just mark it as a test
        email_campaign.is_test = True
        email_campaign.save()
        
        return Response({'status': 'Test email sent', 'recipient': test_email})

    @action(detail=True, methods=['post'])
    def send(self, request, pk=None):
        """Send email campaign."""
        email_campaign = self.get_object()
        
        if email_campaign.is_sent:
            return Response({'error': 'Esta campaña ya fue enviada'}, status=400)
        
        # Calculate recipients
        recipients = set()
        for mailing_list in email_campaign.mailing_lists.all():
            active_members = mailing_list.members.filter(
                is_active=True,
                is_unsubscribed=False
            )
            recipients.update(active_members.values_list('contact_id', flat=True))
        
        email_campaign.recipients_count = len(recipients)
        email_campaign.sent_count = len(recipients)  # In real implementation, track actual sends
        email_campaign.is_sent = True
        email_campaign.sent_date = timezone.now()
        email_campaign.save()
        
        # Update template usage
        email_campaign.email_template.times_used += 1
        email_campaign.email_template.save()
        
        return Response({
            'status': 'Campaign sent',
            'recipients_count': email_campaign.recipients_count
        })

    @action(detail=True, methods=['get'])
    def statistics(self, request, pk=None):
        """Get email campaign statistics."""
        email_campaign = self.get_object()
        
        return Response({
            'campaign_id': email_campaign.id,
            'recipients_count': email_campaign.recipients_count,
            'sent_count': email_campaign.sent_count,
            'opened_count': email_campaign.opened_count,
            'clicked_count': email_campaign.clicked_count,
            'bounced_count': email_campaign.bounced_count,
            'unsubscribed_count': email_campaign.unsubscribed_count,
            'open_rate': email_campaign.open_rate,
            'click_rate': email_campaign.click_rate
        })


class EmailTrackingViewSet(viewsets.ModelViewSet):
    """
    ViewSet for email tracking events.
    """
    queryset = EmailTracking.objects.all()
    serializer_class = EmailTrackingSerializer
    permission_classes = [permissions.IsAuthenticated]
    filter_backends = [DjangoFilterBackend, filters.OrderingFilter]
    filterset_fields = ['email_campaign', 'contact', 'action']
    ordering_fields = ['timestamp']
    ordering = ['-timestamp']

    def create(self, request):
        """Track email event."""
        serializer = self.get_serializer(data=request.data)
        serializer.is_valid(raise_exception=True)
        tracking = serializer.save()
        
        # Update campaign statistics
        campaign = tracking.email_campaign
        if tracking.action == 'opened':
            campaign.opened_count += 1
        elif tracking.action == 'clicked':
            campaign.clicked_count += 1
        elif tracking.action == 'bounced':
            campaign.bounced_count += 1
        elif tracking.action == 'unsubscribed':
            campaign.unsubscribed_count += 1
        campaign.save()
        
        return Response(serializer.data, status=status.HTTP_201_CREATED)


class LeadViewSet(viewsets.ModelViewSet):
    """
    ViewSet for managing leads.
    """
    queryset = Lead.objects.all()
    serializer_class = LeadSerializer
    permission_classes = [permissions.IsAuthenticated]
    filter_backends = [DjangoFilterBackend, filters.SearchFilter, filters.OrderingFilter]
    filterset_fields = ['source', 'status', 'assigned_to', 'is_converted', 'campaign']
    search_fields = ['first_name', 'last_name', 'email', 'company', 'job_title']
    ordering_fields = ['created_at', 'score']
    ordering = ['-created_at']

    def get_queryset(self):
        queryset = super().get_queryset()
        
        # Filter by score range
        min_score = self.request.query_params.get('min_score')
        max_score = self.request.query_params.get('max_score')
        
        if min_score:
            queryset = queryset.filter(score__gte=min_score)
        if max_score:
            queryset = queryset.filter(score__lte=max_score)
        
        return queryset

    @action(detail=True, methods=['post'])
    def qualify(self, request, pk=None):
        """Qualify a lead."""
        lead = self.get_object()
        lead.status = 'qualified'
        lead.save()
        return Response({'status': 'Lead qualified'})

    @action(detail=True, methods=['post'])
    def disqualify(self, request, pk=None):
        """Disqualify a lead."""
        lead = self.get_object()
        lead.status = 'unqualified'
        lead.save()
        return Response({'status': 'Lead disqualified'})

    @action(detail=True, methods=['post'])
    def convert(self, request, pk=None):
        """Convert lead to contact."""
        lead = self.get_object()
        
        if lead.is_converted:
            return Response({'error': 'Este lead ya fue convertido'}, status=400)
        
        # In a real implementation, this would create a Contact and possibly an Account
        # For now, we'll just mark it as converted
        lead.is_converted = True
        lead.status = 'converted'
        lead.converted_date = timezone.now()
        lead.save()
        
        # Update campaign actual leads if applicable
        if lead.campaign:
            lead.campaign.actual_leads += 1
            lead.campaign.save()
        
        return Response({'status': 'Lead converted'})

    @action(detail=True, methods=['post'])
    def recalculate_score(self, request, pk=None):
        """Recalculate lead score."""
        lead = self.get_object()
        old_score = lead.score
        new_score = lead.calculate_score()
        lead.save()
        
        return Response({
            'status': 'Score recalculated',
            'old_score': old_score,
            'new_score': new_score
        })

    @action(detail=False, methods=['get'])
    def score_distribution(self, request):
        """Get lead score distribution."""
        distribution = {
            'high': self.get_queryset().filter(score__gte=70).count(),
            'medium': self.get_queryset().filter(score__gte=40, score__lt=70).count(),
            'low': self.get_queryset().filter(score__lt=40).count()
        }
        return Response(distribution)