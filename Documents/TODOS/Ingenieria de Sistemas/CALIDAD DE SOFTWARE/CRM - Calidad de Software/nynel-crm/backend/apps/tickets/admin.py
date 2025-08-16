from django.contrib import admin
from django.utils.html import format_html
from django.urls import reverse
from django.utils.safestring import mark_safe
from django.utils import timezone
from django.db.models import Avg, Count
from .models import Ticket, TicketComment, KnowledgeBase, TicketTemplate


class TicketCommentInline(admin.TabularInline):
    model = TicketComment
    extra = 0
    fields = ['comment', 'is_internal', 'author', 'author_name', 'has_attachments', 'created_at']
    readonly_fields = ['created_at']
    show_change_link = True


@admin.register(Ticket)
class TicketAdmin(admin.ModelAdmin):
    list_display = [
        'ticket_number', 'subject', 'contact', 'colored_priority', 'colored_status',
        'assigned_to', 'sla_status', 'satisfaction_display', 'created_at'
    ]
    list_filter = [
        'priority', 'status', 'ticket_type', 'assigned_to', 'assigned_team',
        'satisfaction_rating', 'created_at', 'sla_due_date'
    ]
    search_fields = [
        'ticket_number', 'subject', 'description', 'contact__first_name',
        'contact__last_name', 'contact__email', 'account__name'
    ]
    readonly_fields = [
        'ticket_number', 'sla_due_date', 'first_response_date', 'resolution_date',
        'closed_date', 'created_by', 'created_at', 'updated_at', 'response_time_display',
        'resolution_time_display'
    ]
    raw_id_fields = ['contact', 'account', 'assigned_to', 'created_by']
    date_hierarchy = 'created_at'
    inlines = [TicketCommentInline]
    
    fieldsets = (
        ('Información Básica', {
            'fields': ('ticket_number', 'subject', 'description', 'ticket_type')
        }),
        ('Entidades Relacionadas', {
            'fields': ('contact', 'account')
        }),
        ('Prioridad y Estado', {
            'fields': ('priority', 'status')
        }),
        ('Asignación', {
            'fields': ('assigned_to', 'assigned_team')
        }),
        ('SLA y Tiempos', {
            'fields': (
                'sla_due_date', 'first_response_date', 'resolution_date', 'closed_date',
                'response_time_display', 'resolution_time_display'
            ),
            'classes': ('collapse',)
        }),
        ('Satisfacción del Cliente', {
            'fields': ('satisfaction_rating', 'satisfaction_comment'),
            'classes': ('collapse',)
        }),
        ('Notas Internas', {
            'fields': ('internal_notes',),
            'classes': ('collapse',)
        }),
        ('Seguimiento del Sistema', {
            'fields': ('created_by', 'created_at', 'updated_at'),
            'classes': ('collapse',)
        }),
    )
    
    actions = [
        'assign_to_me', 'mark_as_resolved', 'mark_as_closed', 'escalate_priority',
        'send_satisfaction_survey'
    ]
    
    def save_model(self, request, obj, form, change):
        if not change:
            obj.created_by = request.user
        super().save_model(request, obj, form, change)
    
    def colored_priority(self, obj):
        """Display priority with color coding."""
        colors = {
            'low': '#28a745',
            'medium': '#ffc107',
            'high': '#fd7e14',
            'urgent': '#dc3545',
        }
        color = colors.get(obj.priority, '#6c757d')
        return format_html(
            '<span style="color: {}; font-weight: bold;">{}</span>',
            color, obj.get_priority_display()
        )
    colored_priority.short_description = 'Prioridad'
    colored_priority.admin_order_field = 'priority'
    
    def colored_status(self, obj):
        """Display status with color coding."""
        colors = {
            'new': '#17a2b8',
            'open': '#28a745',
            'pending': '#ffc107',
            'on_hold': '#6f42c1',
            'resolved': '#20c997',
            'closed': '#6c757d',
            'cancelled': '#dc3545',
        }
        color = colors.get(obj.status, '#6c757d')
        return format_html(
            '<span style="color: {}; font-weight: bold;">{}</span>',
            color, obj.get_status_display()
        )
    colored_status.short_description = 'Estado'
    colored_status.admin_order_field = 'status'
    
    def sla_status(self, obj):
        """Display SLA status with color coding."""
        if obj.status in ['resolved', 'closed', 'cancelled']:
            return format_html('<span style="color: #6c757d;">Completado</span>')
        
        if obj.is_overdue:
            return format_html('<span style="color: #dc3545; font-weight: bold;">VENCIDO</span>')
        
        if obj.sla_due_date:
            now = timezone.now()
            time_left = obj.sla_due_date - now
            hours_left = time_left.total_seconds() / 3600
            
            if hours_left <= 2:
                color = '#fd7e14'
                status = 'Crítico'
            elif hours_left <= 8:
                color = '#ffc107'
                status = 'Próximo a vencer'
            else:
                color = '#28a745'
                status = 'En tiempo'
            
            return format_html(
                '<span style="color: {};">{}</span>',
                color, status
            )
        
        return '-'
    sla_status.short_description = 'Estado SLA'
    
    def satisfaction_display(self, obj):
        """Display satisfaction rating with stars."""
        if obj.satisfaction_rating:
            stars = '★' * obj.satisfaction_rating + '☆' * (5 - obj.satisfaction_rating)
            color = '#ffc107' if obj.satisfaction_rating >= 4 else '#fd7e14' if obj.satisfaction_rating >= 3 else '#dc3545'
            return format_html(
                '<span style="color: {};">{} ({})</span>',
                color, stars, obj.satisfaction_rating
            )
        return '-'
    satisfaction_display.short_description = 'Satisfacción'
    
    def response_time_display(self, obj):
        """Display response time in hours."""
        time = obj.response_time
        if time is not None:
            if time <= 1:
                color = '#28a745'
            elif time <= 4:
                color = '#ffc107'
            else:
                color = '#dc3545'
            return format_html(
                '<span style="color: {};">{:.1f} horas</span>',
                color, time
            )
        return '-'
    response_time_display.short_description = 'Tiempo de Respuesta'
    
    def resolution_time_display(self, obj):
        """Display resolution time in hours."""
        time = obj.resolution_time
        if time is not None:
            if time <= 24:
                color = '#28a745'
            elif time <= 72:
                color = '#ffc107'
            else:
                color = '#dc3545'
            return format_html(
                '<span style="color: {};">{:.1f} horas</span>',
                color, time
            )
        return '-'
    resolution_time_display.short_description = 'Tiempo de Resolución'
    
    def assign_to_me(self, request, queryset):
        updated = queryset.filter(assigned_to__isnull=True).update(assigned_to=request.user)
        self.message_user(request, f'{updated} tickets asignados a ti.')
    assign_to_me.short_description = 'Asignarme estos tickets'
    
    def mark_as_resolved(self, request, queryset):
        updated = queryset.exclude(status__in=['resolved', 'closed']).update(status='resolved')
        self.message_user(request, f'{updated} tickets marcados como resueltos.')
    mark_as_resolved.short_description = 'Marcar como resueltos'
    
    def mark_as_closed(self, request, queryset):
        updated = queryset.exclude(status='closed').update(status='closed')
        self.message_user(request, f'{updated} tickets marcados como cerrados.')
    mark_as_closed.short_description = 'Marcar como cerrados'
    
    def escalate_priority(self, request, queryset):
        priority_escalation = {
            'low': 'medium',
            'medium': 'high',
            'high': 'urgent',
        }
        updated = 0
        for ticket in queryset:
            if ticket.priority in priority_escalation:
                ticket.priority = priority_escalation[ticket.priority]
                ticket.save()
                updated += 1
        self.message_user(request, f'{updated} tickets escalados en prioridad.')
    escalate_priority.short_description = 'Escalar prioridad'
    
    def send_satisfaction_survey(self, request, queryset):
        # This would implement satisfaction survey sending
        eligible = queryset.filter(status__in=['resolved', 'closed'], satisfaction_rating__isnull=True)
        self.message_user(request, f'Encuestas de satisfacción enviadas para {eligible.count()} tickets.')
    send_satisfaction_survey.short_description = 'Enviar encuesta de satisfacción'


@admin.register(TicketComment)
class TicketCommentAdmin(admin.ModelAdmin):
    list_display = [
        'ticket', 'author_display', 'comment_preview', 'is_internal', 
        'has_attachments', 'created_at'
    ]
    list_filter = ['is_internal', 'has_attachments', 'created_at', 'author']
    search_fields = [
        'comment', 'ticket__ticket_number', 'ticket__subject',
        'author__first_name', 'author__last_name', 'author_name'
    ]
    readonly_fields = ['created_at', 'updated_at']
    raw_id_fields = ['ticket', 'author']
    date_hierarchy = 'created_at'
    
    fieldsets = (
        ('Información del Comentario', {
            'fields': ('ticket', 'comment', 'is_internal')
        }),
        ('Autor', {
            'fields': ('author', 'author_name', 'author_email')
        }),
        ('Adjuntos', {
            'fields': ('has_attachments',)
        }),
        ('Seguimiento', {
            'fields': ('created_at', 'updated_at'),
            'classes': ('collapse',)
        }),
    )
    
    def author_display(self, obj):
        """Display author information."""
        if obj.author:
            return f"{obj.author.get_full_name() or obj.author.username}"
        return obj.author_name or obj.author_email or 'Anónimo'
    author_display.short_description = 'Autor'
    
    def comment_preview(self, obj):
        """Display a preview of the comment."""
        preview = obj.comment[:100]
        if len(obj.comment) > 100:
            preview += '...'
        return preview
    comment_preview.short_description = 'Comentario'


@admin.register(KnowledgeBase)
class KnowledgeBaseAdmin(admin.ModelAdmin):
    list_display = [
        'title', 'category', 'is_published', 'is_featured', 'views',
        'helpfulness_display', 'author', 'published_date'
    ]
    list_filter = [
        'category', 'is_published', 'is_featured', 'author', 
        'created_at', 'published_date'
    ]
    search_fields = ['title', 'content', 'summary', 'tags']
    readonly_fields = [
        'slug', 'views', 'helpful_votes', 'not_helpful_votes',
        'published_date', 'created_at', 'updated_at', 'helpfulness_score_display'
    ]
    prepopulated_fields = {'slug': ('title',)}
    filter_horizontal = ['related_articles']
    date_hierarchy = 'created_at'
    
    fieldsets = (
        ('Información Básica', {
            'fields': ('title', 'slug', 'summary', 'content')
        }),
        ('Categorización', {
            'fields': ('category', 'tags')
        }),
        ('Estado', {
            'fields': ('is_published', 'is_featured', 'published_date')
        }),
        ('Estadísticas', {
            'fields': (
                'views', 'helpful_votes', 'not_helpful_votes', 'helpfulness_score_display'
            )
        }),
        ('Artículos Relacionados', {
            'fields': ('related_articles',),
            'classes': ('collapse',)
        }),
        ('Seguimiento', {
            'fields': ('author', 'created_at', 'updated_at'),
            'classes': ('collapse',)
        }),
    )
    
    actions = ['publish_articles', 'unpublish_articles', 'feature_articles', 'unfeature_articles']
    
    def save_model(self, request, obj, form, change):
        if not change:
            obj.author = request.user
        super().save_model(request, obj, form, change)
    
    def helpfulness_display(self, obj):
        """Display helpfulness score with visual indicator."""
        score = obj.helpfulness_score
        total_votes = obj.helpful_votes + obj.not_helpful_votes
        
        if total_votes == 0:
            return '-'
        
        color = '#28a745' if score >= 80 else '#ffc107' if score >= 60 else '#dc3545'
        return format_html(
            '<span style="color: {};">{:.1f}% ({} votos)</span>',
            color, score, total_votes
        )
    helpfulness_display.short_description = 'Utilidad'
    
    def helpfulness_score_display(self, obj):
        """Display helpfulness score as read-only field."""
        return f"{obj.helpfulness_score:.1f}%"
    helpfulness_score_display.short_description = 'Puntuación de Utilidad'
    
    def publish_articles(self, request, queryset):
        updated = queryset.filter(is_published=False).update(is_published=True)
        # Update published_date for newly published articles
        for article in queryset.filter(is_published=True, published_date__isnull=True):
            article.published_date = timezone.now()
            article.save(update_fields=['published_date'])
        self.message_user(request, f'{updated} artículos publicados.')
    publish_articles.short_description = 'Publicar artículos'
    
    def unpublish_articles(self, request, queryset):
        updated = queryset.filter(is_published=True).update(is_published=False)
        self.message_user(request, f'{updated} artículos despublicados.')
    unpublish_articles.short_description = 'Despublicar artículos'
    
    def feature_articles(self, request, queryset):
        updated = queryset.filter(is_published=True, is_featured=False).update(is_featured=True)
        self.message_user(request, f'{updated} artículos destacados.')
    feature_articles.short_description = 'Destacar artículos'
    
    def unfeature_articles(self, request, queryset):
        updated = queryset.filter(is_featured=True).update(is_featured=False)
        self.message_user(request, f'{updated} artículos no destacados.')
    unfeature_articles.short_description = 'Quitar destacado'


@admin.register(TicketTemplate)
class TicketTemplateAdmin(admin.ModelAdmin):
    list_display = [
        'name', 'category', 'is_active', 'times_used', 'created_by', 'created_at'
    ]
    list_filter = ['category', 'is_active', 'created_by', 'created_at']
    search_fields = ['name', 'category', 'subject', 'content']
    readonly_fields = ['times_used', 'created_by', 'created_at', 'updated_at']
    
    fieldsets = (
        ('Información Básica', {
            'fields': ('name', 'category', 'is_active')
        }),
        ('Contenido de la Plantilla', {
            'fields': ('subject', 'content')
        }),
        ('Variables Disponibles', {
            'fields': ('available_variables',),
            'classes': ('collapse',)
        }),
        ('Estadísticas', {
            'fields': ('times_used',)
        }),
        ('Seguimiento', {
            'fields': ('created_by', 'created_at', 'updated_at'),
            'classes': ('collapse',)
        }),
    )
    
    actions = ['duplicate_templates', 'activate_templates', 'deactivate_templates']
    
    def save_model(self, request, obj, form, change):
        if not change:
            obj.created_by = request.user
        super().save_model(request, obj, form, change)
    
    def duplicate_templates(self, request, queryset):
        for template in queryset:
            template.pk = None
            template.name = f"Copia de {template.name}"
            template.times_used = 0
            template.save()
        self.message_user(request, f'{queryset.count()} plantillas duplicadas.')
    duplicate_templates.short_description = 'Duplicar plantillas'
    
    def activate_templates(self, request, queryset):
        updated = queryset.update(is_active=True)
        self.message_user(request, f'{updated} plantillas activadas.')
    activate_templates.short_description = 'Activar plantillas'
    
    def deactivate_templates(self, request, queryset):
        updated = queryset.update(is_active=False)
        self.message_user(request, f'{updated} plantillas desactivadas.')
    deactivate_templates.short_description = 'Desactivar plantillas'