from rest_framework import serializers
from .models import Ticket, TicketComment, KnowledgeBase, TicketTemplate
from apps.contacts.models import Contact, Account
from django.contrib.auth import get_user_model

User = get_user_model()


class TicketSerializer(serializers.ModelSerializer):
    contact_name = serializers.SerializerMethodField()
    contact_email = serializers.CharField(source='contact.email', read_only=True)
    account_name = serializers.CharField(source='account.name', read_only=True)
    assigned_to_name = serializers.CharField(source='assigned_to.get_full_name', read_only=True)
    created_by_name = serializers.CharField(source='created_by.get_full_name', read_only=True)
    priority_display = serializers.CharField(source='get_priority_display', read_only=True)
    status_display = serializers.CharField(source='get_status_display', read_only=True)
    ticket_type_display = serializers.CharField(source='get_ticket_type_display', read_only=True)
    is_overdue = serializers.ReadOnlyField()
    response_time = serializers.ReadOnlyField()
    resolution_time = serializers.ReadOnlyField()
    comments_count = serializers.IntegerField(source='comments.count', read_only=True)
    
    class Meta:
        model = Ticket
        fields = '__all__'
        read_only_fields = [
            'ticket_number', 'created_at', 'updated_at', 'first_response_date',
            'resolution_date', 'closed_date'
        ]
    
    def get_contact_name(self, obj):
        return f"{obj.contact.first_name} {obj.contact.last_name}"
    
    def validate(self, attrs):
        """Validate ticket data."""
        # Ensure contact belongs to account if both are specified
        contact = attrs.get('contact', self.instance.contact if self.instance else None)
        account = attrs.get('account', self.instance.account if self.instance else None)
        
        if contact and account and contact.account != account:
            raise serializers.ValidationError({
                'contact': 'El contacto no pertenece a la cuenta especificada'
            })
        
        return attrs


class TicketCommentSerializer(serializers.ModelSerializer):
    ticket_number = serializers.CharField(source='ticket.ticket_number', read_only=True)
    author_name = serializers.CharField(read_only=True)
    author_email = serializers.CharField(read_only=True)
    
    class Meta:
        model = TicketComment
        fields = '__all__'
        read_only_fields = ['created_at', 'updated_at', 'author_name', 'author_email']
    
    def validate_is_internal(self, value):
        """Only staff can create internal notes."""
        if value and not self.context['request'].user.is_staff:
            raise serializers.ValidationError(
                "Solo el personal puede crear notas internas"
            )
        return value


class KnowledgeBaseSerializer(serializers.ModelSerializer):
    author_name = serializers.CharField(source='author.get_full_name', read_only=True)
    category_display = serializers.CharField(source='get_category_display', read_only=True)
    helpfulness_score = serializers.ReadOnlyField()
    tags_list = serializers.SerializerMethodField()
    
    class Meta:
        model = KnowledgeBase
        fields = '__all__'
        read_only_fields = [
            'slug', 'views', 'helpful_votes', 'not_helpful_votes',
            'created_at', 'updated_at', 'published_date'
        ]
    
    def get_tags_list(self, obj):
        if obj.tags:
            return [tag.strip() for tag in obj.tags.split(',')]
        return []
    
    def validate_slug(self, value):
        """Ensure slug is unique."""
        if KnowledgeBase.objects.filter(slug=value).exclude(
            pk=self.instance.pk if self.instance else None
        ).exists():
            raise serializers.ValidationError("Este slug ya está en uso")
        return value
    
    def create(self, validated_data):
        """Auto-generate slug if not provided."""
        if not validated_data.get('slug'):
            from django.utils.text import slugify
            base_slug = slugify(validated_data['title'])
            slug = base_slug
            counter = 1
            
            while KnowledgeBase.objects.filter(slug=slug).exists():
                slug = f"{base_slug}-{counter}"
                counter += 1
            
            validated_data['slug'] = slug
        
        return super().create(validated_data)


class TicketTemplateSerializer(serializers.ModelSerializer):
    created_by_name = serializers.CharField(source='created_by.get_full_name', read_only=True)
    
    class Meta:
        model = TicketTemplate
        fields = '__all__'
        read_only_fields = ['times_used', 'created_at', 'updated_at']
    
    def validate_name(self, value):
        """Ensure template name is unique within category."""
        category = self.initial_data.get('category', '')
        existing = TicketTemplate.objects.filter(
            name=value,
            category=category
        ).exclude(pk=self.instance.pk if self.instance else None)
        
        if existing.exists():
            raise serializers.ValidationError(
                f"Ya existe una plantilla con este nombre en la categoría '{category}'"
            )
        
        return value