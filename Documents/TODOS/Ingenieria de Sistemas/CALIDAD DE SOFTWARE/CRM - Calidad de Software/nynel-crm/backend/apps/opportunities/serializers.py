from rest_framework import serializers
from .models import Opportunity, OpportunityProduct, Quote, Commission
from apps.contacts.models import Account, Contact
from apps.marketing.models import Campaign
from django.contrib.auth import get_user_model

User = get_user_model()


class OpportunitySerializer(serializers.ModelSerializer):
    account_name = serializers.CharField(source='account.name', read_only=True)
    contact_name = serializers.SerializerMethodField()
    assigned_to_name = serializers.CharField(source='assigned_to.get_full_name', read_only=True)
    campaign_name = serializers.CharField(source='campaign.name', read_only=True)
    stage_display = serializers.CharField(source='get_stage_display', read_only=True)
    product_type_display = serializers.CharField(source='get_product_type_display', read_only=True)
    lead_source_display = serializers.CharField(source='get_lead_source_display', read_only=True)
    region_display = serializers.CharField(source='get_region_display', read_only=True)
    
    class Meta:
        model = Opportunity
        fields = '__all__'
        read_only_fields = ['opportunity_id', 'expected_revenue', 'created_at', 'updated_at']
    
    def get_contact_name(self, obj):
        if obj.contact:
            return f"{obj.contact.first_name} {obj.contact.last_name}"
        return None
    
    def validate_probability(self, value):
        """Ensure probability is within valid range."""
        if value < 0 or value > 100:
            raise serializers.ValidationError("La probabilidad debe estar entre 0 y 100")
        return value
    
    def validate(self, attrs):
        """Validate stage and probability consistency."""
        stage = attrs.get('stage', self.instance.stage if self.instance else None)
        probability = attrs.get('probability', self.instance.probability if self.instance else None)
        
        if stage and probability is not None:
            if stage == 'closed_won' and probability != 100:
                attrs['probability'] = 100
            elif stage == 'closed_lost' and probability != 0:
                attrs['probability'] = 0
        
        return attrs


class OpportunityProductSerializer(serializers.ModelSerializer):
    opportunity_name = serializers.CharField(source='opportunity.name', read_only=True)
    
    class Meta:
        model = OpportunityProduct
        fields = '__all__'
        read_only_fields = ['total_price', 'created_at', 'updated_at']
    
    def validate_discount(self, value):
        """Ensure discount is within valid range."""
        if value < 0 or value > 100:
            raise serializers.ValidationError("El descuento debe estar entre 0 y 100")
        return value


class QuoteSerializer(serializers.ModelSerializer):
    opportunity_name = serializers.CharField(source='opportunity.name', read_only=True)
    account_name = serializers.CharField(source='opportunity.account.name', read_only=True)
    status_display = serializers.CharField(source='get_status_display', read_only=True)
    
    class Meta:
        model = Quote
        fields = '__all__'
        read_only_fields = ['quote_number', 'tax_amount', 'total', 'created_at', 'updated_at']
    
    def validate(self, attrs):
        """Validate expiration date is after issue date."""
        issue_date = attrs.get('issue_date', self.instance.issue_date if self.instance else None)
        expiration_date = attrs.get('expiration_date', self.instance.expiration_date if self.instance else None)
        
        if issue_date and expiration_date and expiration_date <= issue_date:
            raise serializers.ValidationError({
                'expiration_date': 'La fecha de vencimiento debe ser posterior a la fecha de emisión'
            })
        
        return attrs


class CommissionSerializer(serializers.ModelSerializer):
    opportunity_name = serializers.CharField(source='opportunity.name', read_only=True)
    opportunity_id = serializers.CharField(source='opportunity.opportunity_id', read_only=True)
    sales_rep_name = serializers.CharField(source='sales_rep.get_full_name', read_only=True)
    
    class Meta:
        model = Commission
        fields = '__all__'
        read_only_fields = ['commission_amount', 'created_at', 'updated_at']
    
    def validate_commission_rate(self, value):
        """Ensure commission rate is within valid range."""
        if value < 0 or value > 100:
            raise serializers.ValidationError("La tasa de comisión debe estar entre 0 y 100")
        return value