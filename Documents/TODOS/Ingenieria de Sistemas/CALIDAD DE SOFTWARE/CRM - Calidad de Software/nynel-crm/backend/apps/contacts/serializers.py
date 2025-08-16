from rest_framework import serializers
from .models import Account, Contact, Activity


class AccountSerializer(serializers.ModelSerializer):
    """Serializer for Account model."""
    contacts_count = serializers.SerializerMethodField()
    opportunities_count = serializers.SerializerMethodField()
    created_by_name = serializers.CharField(source='created_by.get_full_name', read_only=True)
    assigned_to_name = serializers.CharField(source='assigned_to.get_full_name', read_only=True)
    
    class Meta:
        model = Account
        fields = [
            'id', 'name', 'account_type', 'industry', 'annual_revenue', 
            'employees', 'website', 'ruc', 'billing_street', 'billing_city',
            'billing_state', 'billing_postal_code', 'billing_country',
            'created_by', 'created_by_name', 'assigned_to', 'assigned_to_name',
            'created_at', 'updated_at', 'contacts_count', 'opportunities_count'
        ]
        read_only_fields = ['created_by', 'created_at', 'updated_at']
    
    def get_contacts_count(self, obj):
        return obj.contacts.count()
    
    def get_opportunities_count(self, obj):
        return obj.opportunities.count()
    
    def validate_ruc(self, value):
        """Validate RUC format."""
        if not value.isdigit() or len(value) != 11:
            raise serializers.ValidationError("RUC debe tener exactamente 11 dígitos.")
        return value


class ContactSerializer(serializers.ModelSerializer):
    """Serializer for Contact model."""
    full_name = serializers.CharField(read_only=True)
    account_name = serializers.CharField(source='account.name', read_only=True)
    created_by_name = serializers.CharField(source='created_by.get_full_name', read_only=True)
    assigned_to_name = serializers.CharField(source='assigned_to.get_full_name', read_only=True)
    activities_count = serializers.SerializerMethodField()
    opportunities_count = serializers.SerializerMethodField()
    
    class Meta:
        model = Contact
        fields = [
            'id', 'salutation', 'first_name', 'last_name', 'full_name',
            'job_title', 'department', 'email', 'phone', 'mobile', 'dni',
            'account', 'account_name', 'reports_to', 'street', 'city',
            'state', 'postal_code', 'country', 'preferred_contact_method',
            'do_not_call', 'do_not_email', 'created_by', 'created_by_name',
            'assigned_to', 'assigned_to_name', 'created_at', 'updated_at',
            'last_activity_date', 'activities_count', 'opportunities_count'
        ]
        read_only_fields = ['created_by', 'created_at', 'updated_at', 'last_activity_date']
    
    def get_activities_count(self, obj):
        return obj.activities.count()
    
    def get_opportunities_count(self, obj):
        return obj.opportunities.count()
    
    def validate_email(self, value):
        """Validate email uniqueness."""
        if Contact.objects.filter(email=value).exclude(pk=self.instance.pk if self.instance else None).exists():
            raise serializers.ValidationError("Ya existe un contacto con este email.")
        return value
    
    def validate_dni(self, value):
        """Validate DNI format if provided."""
        if value and (not value.isdigit() or len(value) != 8):
            raise serializers.ValidationError("DNI debe tener exactamente 8 dígitos.")
        return value


class ActivitySerializer(serializers.ModelSerializer):
    """Serializer for Activity model."""
    contact_name = serializers.CharField(source='contact.full_name', read_only=True)
    account_name = serializers.CharField(source='account.name', read_only=True)
    assigned_to_name = serializers.CharField(source='assigned_to.get_full_name', read_only=True)
    created_by_name = serializers.CharField(source='created_by.get_full_name', read_only=True)
    is_overdue = serializers.SerializerMethodField()
    
    class Meta:
        model = Activity
        fields = [
            'id', 'activity_type', 'subject', 'description', 'contact',
            'contact_name', 'account', 'account_name', 'due_date',
            'duration', 'status', 'completed_date', 'assigned_to',
            'assigned_to_name', 'created_by', 'created_by_name',
            'created_at', 'updated_at', 'is_overdue'
        ]
        read_only_fields = ['created_by', 'created_at', 'updated_at', 'completed_date']
    
    def get_is_overdue(self, obj):
        """Check if activity is overdue."""
        from django.utils import timezone
        if obj.status != 'completed' and obj.due_date:
            return timezone.now() > obj.due_date
        return False
    
    def validate(self, data):
        """Validate that either contact or account is provided."""
        if not data.get('contact') and not data.get('account'):
            raise serializers.ValidationError("Debe especificar un contacto o una cuenta.")
        return data


class ContactListSerializer(serializers.ModelSerializer):
    """Lightweight serializer for contact lists."""
    full_name = serializers.CharField(read_only=True)
    account_name = serializers.CharField(source='account.name', read_only=True)
    
    class Meta:
        model = Contact
        fields = [
            'id', 'first_name', 'last_name', 'full_name', 'email',
            'phone', 'job_title', 'account_name', 'created_at'
        ]


class AccountListSerializer(serializers.ModelSerializer):
    """Lightweight serializer for account lists."""
    contacts_count = serializers.SerializerMethodField()
    
    class Meta:
        model = Account
        fields = [
            'id', 'name', 'account_type', 'industry', 'website',
            'contacts_count', 'created_at'
        ]
    
    def get_contacts_count(self, obj):
        return obj.contacts.count()