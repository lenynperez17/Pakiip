from rest_framework import serializers
from .models import (
    Campaign, EmailTemplate, MailingList, MailingListMember,
    EmailCampaign, EmailTracking, Lead
)
from apps.contacts.models import Contact
from django.contrib.auth import get_user_model

User = get_user_model()


class CampaignSerializer(serializers.ModelSerializer):
    created_by_name = serializers.CharField(source='created_by.get_full_name', read_only=True)
    assigned_to_name = serializers.CharField(source='assigned_to.get_full_name', read_only=True)
    campaign_type_display = serializers.CharField(source='get_campaign_type_display', read_only=True)
    status_display = serializers.CharField(source='get_status_display', read_only=True)
    roi = serializers.ReadOnlyField()
    leads_count = serializers.IntegerField(source='leads.count', read_only=True)
    opportunities_count = serializers.IntegerField(source='opportunities.count', read_only=True)
    
    class Meta:
        model = Campaign
        fields = '__all__'
        read_only_fields = ['created_at', 'updated_at']
    
    def validate(self, attrs):
        """Validate campaign dates."""
        start_date = attrs.get('start_date', self.instance.start_date if self.instance else None)
        end_date = attrs.get('end_date', self.instance.end_date if self.instance else None)
        
        if start_date and end_date and end_date < start_date:
            raise serializers.ValidationError({
                'end_date': 'La fecha de fin debe ser posterior a la fecha de inicio'
            })
        
        return attrs


class EmailTemplateSerializer(serializers.ModelSerializer):
    created_by_name = serializers.CharField(source='created_by.get_full_name', read_only=True)
    
    class Meta:
        model = EmailTemplate
        fields = '__all__'
        read_only_fields = ['times_used', 'created_at', 'updated_at']


class MailingListSerializer(serializers.ModelSerializer):
    created_by_name = serializers.CharField(source='created_by.get_full_name', read_only=True)
    active_members_count = serializers.SerializerMethodField()
    
    class Meta:
        model = MailingList
        fields = '__all__'
        read_only_fields = ['member_count', 'created_at', 'updated_at']
    
    def get_active_members_count(self, obj):
        return obj.members.filter(is_active=True, is_unsubscribed=False).count()


class MailingListMemberSerializer(serializers.ModelSerializer):
    mailing_list_name = serializers.CharField(source='mailing_list.name', read_only=True)
    contact_name = serializers.SerializerMethodField()
    contact_email = serializers.CharField(source='contact.email', read_only=True)
    added_by_name = serializers.CharField(source='added_by.get_full_name', read_only=True)
    
    class Meta:
        model = MailingListMember
        fields = '__all__'
        read_only_fields = ['added_date', 'unsubscribed_date']
    
    def get_contact_name(self, obj):
        return f"{obj.contact.first_name} {obj.contact.last_name}"
    
    def validate(self, attrs):
        """Ensure unique contact per mailing list."""
        mailing_list = attrs.get('mailing_list')
        contact = attrs.get('contact')
        
        if mailing_list and contact:
            existing = MailingListMember.objects.filter(
                mailing_list=mailing_list,
                contact=contact
            ).exclude(pk=self.instance.pk if self.instance else None)
            
            if existing.exists():
                raise serializers.ValidationError({
                    'contact': 'Este contacto ya está en la lista de correo'
                })
        
        return attrs


class EmailCampaignSerializer(serializers.ModelSerializer):
    campaign_name = serializers.CharField(source='campaign.name', read_only=True)
    email_template_name = serializers.CharField(source='email_template.name', read_only=True)
    mailing_lists_names = serializers.SerializerMethodField()
    open_rate = serializers.ReadOnlyField()
    click_rate = serializers.ReadOnlyField()
    
    class Meta:
        model = EmailCampaign
        fields = '__all__'
        read_only_fields = [
            'recipients_count', 'sent_count', 'opened_count', 'clicked_count',
            'bounced_count', 'unsubscribed_count', 'sent_date', 'created_at', 'updated_at'
        ]
    
    def get_mailing_lists_names(self, obj):
        return list(obj.mailing_lists.values_list('name', flat=True))
    
    def validate_scheduled_date(self, value):
        """Ensure scheduled date is in the future."""
        from django.utils import timezone
        if value < timezone.now():
            raise serializers.ValidationError("La fecha programada debe ser en el futuro")
        return value


class EmailTrackingSerializer(serializers.ModelSerializer):
    email_campaign_name = serializers.CharField(source='email_campaign.campaign.name', read_only=True)
    contact_name = serializers.SerializerMethodField()
    contact_email = serializers.CharField(source='contact.email', read_only=True)
    action_display = serializers.CharField(source='get_action_display', read_only=True)
    
    class Meta:
        model = EmailTracking
        fields = '__all__'
        read_only_fields = ['tracking_id', 'timestamp']
    
    def get_contact_name(self, obj):
        return f"{obj.contact.first_name} {obj.contact.last_name}"


class LeadSerializer(serializers.ModelSerializer):
    assigned_to_name = serializers.CharField(source='assigned_to.get_full_name', read_only=True)
    campaign_name = serializers.CharField(source='campaign.name', read_only=True)
    source_display = serializers.CharField(source='get_source_display', read_only=True)
    status_display = serializers.CharField(source='get_status_display', read_only=True)
    full_name = serializers.SerializerMethodField()
    
    class Meta:
        model = Lead
        fields = '__all__'
        read_only_fields = [
            'score', 'is_converted', 'converted_contact', 'converted_date',
            'created_at', 'updated_at'
        ]
    
    def get_full_name(self, obj):
        return f"{obj.first_name} {obj.last_name}"
    
    def validate_email(self, value):
        """Ensure email is unique among non-converted leads."""
        existing = Lead.objects.filter(
            email=value,
            is_converted=False
        ).exclude(pk=self.instance.pk if self.instance else None)
        
        if existing.exists():
            raise serializers.ValidationError(
                "Ya existe un lead con este email que no ha sido convertido"
            )
        
        return value