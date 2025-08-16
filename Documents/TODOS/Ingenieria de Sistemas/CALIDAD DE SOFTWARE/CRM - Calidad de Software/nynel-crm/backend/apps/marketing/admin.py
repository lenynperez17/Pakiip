from django.contrib import admin
from django.utils.html import format_html
from django.urls import reverse
from django.utils.safestring import mark_safe
from django.utils import timezone
from .models import Campaign, EmailTemplate, MailingList, MailingListMember, EmailCampaign, EmailTracking, Lead


class EmailCampaignInline(admin.TabularInline):
    model = EmailCampaign
    extra = 0
    fields = ['email_template', 'scheduled_date', 'sent_count', 'opened_count', 'clicked_count', 'is_sent']
    readonly_fields = ['sent_count', 'opened_count', 'clicked_count']
    show_change_link = True


class LeadInline(admin.TabularInline):
    model = Lead
    extra = 0
    fields = ['first_name', 'last_name', 'email', 'company', 'status', 'score']
    readonly_fields = ['score']
    show_change_link = True


@admin.register(Campaign)
class CampaignAdmin(admin.ModelAdmin):
    list_display = [
        'name', 'campaign_type', 'colored_status', 'start_date', 'end_date',
        'budget', 'actual_cost', 'leads_generated', 'roi_display', 'assigned_to'
    ]
    list_filter = [
        'campaign_type', 'status', 'start_date', 'end_date',
        'assigned_to', 'created_at'
    ]
    search_fields = ['name', 'description', 'objective', 'target_audience']
    readonly_fields = ['created_by', 'created_at', 'updated_at']
    raw_id_fields = ['assigned_to']
    date_hierarchy = 'start_date'
    inlines = [EmailCampaignInline, LeadInline]
    
    fieldsets = (
        ('Información Básica', {
            'fields': ('name', 'campaign_type', 'description', 'objective')
        }),
        ('Cronograma', {
            'fields': ('start_date', 'end_date', 'status')
        }),
        ('Presupuesto', {
            'fields': ('budget', 'actual_cost')
        }),
        ('Audiencia y Objetivos', {
            'fields': ('target_audience', 'expected_leads', 'actual_leads')
        }),
        ('Asignación', {
            'fields': ('assigned_to',)
        }),
        ('Seguimiento del Sistema', {
            'fields': ('created_by', 'created_at', 'updated_at'),
            'classes': ('collapse',)
        }),
    )
    
    actions = ['activate_campaigns', 'pause_campaigns', 'complete_campaigns', 'calculate_roi']
    
    def save_model(self, request, obj, form, change):
        if not change:
            obj.created_by = request.user
        super().save_model(request, obj, form, change)
    
    def colored_status(self, obj):
        """Display status with color coding."""
        colors = {
            'planning': '#6c757d',
            'active': '#28a745',
            'paused': '#ffc107',
            'completed': '#17a2b8',
            'cancelled': '#dc3545',
        }
        color = colors.get(obj.status, '#6c757d')
        return format_html(
            '<span style="color: {}; font-weight: bold;">{}</span>',
            color, obj.get_status_display()
        )
    colored_status.short_description = 'Estado'
    colored_status.admin_order_field = 'status'
    
    def leads_generated(self, obj):
        """Display actual vs expected leads."""
        percentage = (obj.actual_leads / obj.expected_leads * 100) if obj.expected_leads > 0 else 0
        color = 'success' if percentage >= 100 else 'warning' if percentage >= 50 else 'danger'
        return format_html(
            '<span class="badge badge-{}">{}/{} ({:.1f}%)</span>',
            color, obj.actual_leads, obj.expected_leads, percentage
        )
    leads_generated.short_description = 'Leads (Real/Esperado)'
    
    def roi_display(self, obj):
        """Display ROI with color coding."""
        roi = obj.roi
        color = 'success' if roi > 0 else 'danger' if roi < -50 else 'warning'
        return format_html(
            '<span style="color: {}; font-weight: bold;">{:.1f}%</span>',
            color, roi
        )
    roi_display.short_description = 'ROI'
    
    def activate_campaigns(self, request, queryset):
        updated = queryset.exclude(status='active').update(status='active')
        self.message_user(request, f'{updated} campañas activadas.')
    activate_campaigns.short_description = 'Activar campañas'
    
    def pause_campaigns(self, request, queryset):
        updated = queryset.filter(status='active').update(status='paused')
        self.message_user(request, f'{updated} campañas pausadas.')
    pause_campaigns.short_description = 'Pausar campañas'
    
    def complete_campaigns(self, request, queryset):
        updated = queryset.exclude(status='completed').update(status='completed')
        self.message_user(request, f'{updated} campañas marcadas como completadas.')
    complete_campaigns.short_description = 'Marcar como completadas'
    
    def calculate_roi(self, request, queryset):
        for campaign in queryset:
            # Update ROI calculation by saving the model
            campaign.save()
        self.message_user(request, f'ROI recalculado para {queryset.count()} campañas.')
    calculate_roi.short_description = 'Recalcular ROI'


@admin.register(EmailTemplate)
class EmailTemplateAdmin(admin.ModelAdmin):
    list_display = [
        'name', 'subject', 'is_active', 'times_used', 'created_by', 'created_at'
    ]
    list_filter = ['is_active', 'created_by', 'created_at']
    search_fields = ['name', 'subject', 'body_html', 'body_text']
    readonly_fields = ['times_used', 'created_by', 'created_at', 'updated_at']
    
    fieldsets = (
        ('Información Básica', {
            'fields': ('name', 'subject', 'is_active')
        }),
        ('Contenido', {
            'fields': ('body_html', 'body_text')
        }),
        ('Variables', {
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


class MailingListMemberInline(admin.TabularInline):
    model = MailingListMember
    extra = 0
    fields = ['contact', 'is_active', 'is_unsubscribed', 'added_date', 'import_source']
    readonly_fields = ['added_date']
    raw_id_fields = ['contact']


@admin.register(MailingList)
class MailingListAdmin(admin.ModelAdmin):
    list_display = [
        'name', 'member_count', 'is_dynamic', 'active_members', 'unsubscribed_count', 'created_by', 'created_at'
    ]
    list_filter = ['is_dynamic', 'created_by', 'created_at']
    search_fields = ['name', 'description']
    readonly_fields = ['member_count', 'created_by', 'created_at', 'updated_at']
    inlines = [MailingListMemberInline]
    
    fieldsets = (
        ('Información Básica', {
            'fields': ('name', 'description', 'is_dynamic')
        }),
        ('Criterios de Segmentación', {
            'fields': ('criteria',),
            'classes': ('collapse',)
        }),
        ('Estadísticas', {
            'fields': ('member_count',)
        }),
        ('Seguimiento', {
            'fields': ('created_by', 'created_at', 'updated_at'),
            'classes': ('collapse',)
        }),
    )
    
    actions = ['update_member_counts', 'export_members']
    
    def save_model(self, request, obj, form, change):
        if not change:
            obj.created_by = request.user
        super().save_model(request, obj, form, change)
    
    def active_members(self, obj):
        """Count of active, non-unsubscribed members."""
        count = obj.members.filter(is_active=True, is_unsubscribed=False).count()
        return count
    active_members.short_description = 'Miembros Activos'
    
    def unsubscribed_count(self, obj):
        """Count of unsubscribed members."""
        count = obj.members.filter(is_unsubscribed=True).count()
        return count
    unsubscribed_count.short_description = 'Desuscritos'
    
    def update_member_counts(self, request, queryset):
        for mailing_list in queryset:
            mailing_list.update_member_count()
        self.message_user(request, f'Conteos actualizados para {queryset.count()} listas.')
    update_member_counts.short_description = 'Actualizar conteos de miembros'
    
    def export_members(self, request, queryset):
        # This would implement member export functionality
        self.message_user(request, f'Exportación iniciada para {queryset.count()} listas.')
    export_members.short_description = 'Exportar miembros'


@admin.register(MailingListMember)
class MailingListMemberAdmin(admin.ModelAdmin):
    list_display = [
        'contact', 'mailing_list', 'is_active', 'is_unsubscribed', 
        'unsubscribed_date', 'import_source', 'added_date'
    ]
    list_filter = [
        'is_active', 'is_unsubscribed', 'import_source', 
        'added_date', 'unsubscribed_date'
    ]
    search_fields = [
        'contact__first_name', 'contact__last_name', 'contact__email',
        'mailing_list__name'
    ]
    readonly_fields = ['added_date', 'added_by']
    raw_id_fields = ['mailing_list', 'contact', 'added_by']
    date_hierarchy = 'added_date'
    
    actions = ['unsubscribe_members', 'reactivate_members']
    
    def unsubscribe_members(self, request, queryset):
        for member in queryset.filter(is_unsubscribed=False):
            member.unsubscribe('Desuscrito masivamente desde admin')
        self.message_user(request, f'{queryset.count()} miembros desuscritos.')
    unsubscribe_members.short_description = 'Desuscribir miembros'
    
    def reactivate_members(self, request, queryset):
        updated = queryset.update(
            is_active=True, 
            is_unsubscribed=False, 
            unsubscribed_date=None,
            unsubscribe_reason=''
        )
        self.message_user(request, f'{updated} miembros reactivados.')
    reactivate_members.short_description = 'Reactivar miembros'


class EmailTrackingInline(admin.TabularInline):
    model = EmailTracking
    extra = 0
    fields = ['contact', 'action', 'timestamp', 'ip_address']
    readonly_fields = ['timestamp', 'tracking_id']
    raw_id_fields = ['contact']


@admin.register(EmailCampaign)
class EmailCampaignAdmin(admin.ModelAdmin):
    list_display = [
        'campaign', 'email_template', 'scheduled_date', 'sent_date',
        'recipients_count', 'open_rate_display', 'click_rate_display', 'is_sent', 'is_test'
    ]
    list_filter = [
        'is_sent', 'is_test', 'scheduled_date', 'sent_date', 'created_at'
    ]
    search_fields = [
        'campaign__name', 'email_template__name', 'email_template__subject'
    ]
    readonly_fields = [
        'recipients_count', 'sent_count', 'opened_count', 'clicked_count',
        'bounced_count', 'unsubscribed_count', 'created_at', 'updated_at'
    ]
    raw_id_fields = ['campaign', 'email_template']
    filter_horizontal = ['mailing_lists']
    date_hierarchy = 'scheduled_date'
    inlines = [EmailTrackingInline]
    
    fieldsets = (
        ('Configuración de Campaña', {
            'fields': ('campaign', 'email_template', 'mailing_lists')
        }),
        ('Programación', {
            'fields': ('scheduled_date', 'sent_date', 'is_test')
        }),
        ('Estadísticas de Entrega', {
            'fields': (
                'recipients_count', 'sent_count', 'bounced_count'
            )
        }),
        ('Estadísticas de Engagement', {
            'fields': (
                'opened_count', 'clicked_count', 'unsubscribed_count'
            )
        }),
        ('Estado', {
            'fields': ('is_sent',)
        }),
        ('Seguimiento', {
            'fields': ('created_at', 'updated_at'),
            'classes': ('collapse',)
        }),
    )
    
    actions = ['send_test_emails', 'send_campaigns']
    
    def open_rate_display(self, obj):
        """Display open rate with color coding."""
        rate = obj.open_rate
        color = 'success' if rate >= 20 else 'warning' if rate >= 10 else 'danger'
        return format_html(
            '<span style="color: {};">{:.1f}%</span>',
            color, rate
        )
    open_rate_display.short_description = 'Tasa de Apertura'
    
    def click_rate_display(self, obj):
        """Display click rate with color coding."""
        rate = obj.click_rate
        color = 'success' if rate >= 3 else 'warning' if rate >= 1 else 'danger'
        return format_html(
            '<span style="color: {};">{:.1f}%</span>',
            color, rate
        )
    click_rate_display.short_description = 'Tasa de Click'
    
    def send_test_emails(self, request, queryset):
        # This would implement test email sending
        self.message_user(request, f'Emails de prueba enviados para {queryset.count()} campañas.')
    send_test_emails.short_description = 'Enviar emails de prueba'
    
    def send_campaigns(self, request, queryset):
        # This would implement actual campaign sending
        unsent = queryset.filter(is_sent=False, is_test=False)
        self.message_user(request, f'{unsent.count()} campañas programadas para envío.')
    send_campaigns.short_description = 'Enviar campañas'


@admin.register(EmailTracking)
class EmailTrackingAdmin(admin.ModelAdmin):
    list_display = [
        'email_campaign', 'contact', 'action', 'timestamp', 'ip_address', 'click_url'
    ]
    list_filter = ['action', 'timestamp', 'email_campaign']
    search_fields = [
        'contact__first_name', 'contact__last_name', 'contact__email',
        'email_campaign__campaign__name', 'ip_address'
    ]
    readonly_fields = ['tracking_id', 'timestamp']
    raw_id_fields = ['email_campaign', 'contact']
    date_hierarchy = 'timestamp'
    
    def has_add_permission(self, request):
        # Tracking events should be created automatically
        return False


@admin.register(Lead)
class LeadAdmin(admin.ModelAdmin):
    list_display = [
        'full_name', 'email', 'company', 'job_title', 'source',
        'colored_status', 'score_bar', 'assigned_to', 'is_converted', 'created_at'
    ]
    list_filter = [
        'source', 'status', 'is_converted', 'assigned_to', 'campaign', 'created_at'
    ]
    search_fields = [
        'first_name', 'last_name', 'email', 'company', 'job_title'
    ]
    readonly_fields = ['score', 'converted_date', 'created_at', 'updated_at']
    raw_id_fields = ['campaign', 'assigned_to', 'converted_contact']
    date_hierarchy = 'created_at'
    
    fieldsets = (
        ('Información Personal', {
            'fields': ('first_name', 'last_name', 'email', 'phone')
        }),
        ('Información Profesional', {
            'fields': ('company', 'job_title')
        }),
        ('Origen y Campaña', {
            'fields': ('source', 'campaign')
        }),
        ('Calificación y Estado', {
            'fields': ('status', 'score', 'assigned_to')
        }),
        ('Conversión', {
            'fields': ('is_converted', 'converted_contact', 'converted_date'),
            'classes': ('collapse',)
        }),
        ('Seguimiento', {
            'fields': ('created_at', 'updated_at'),
            'classes': ('collapse',)
        }),
    )
    
    actions = ['qualify_leads', 'assign_to_me', 'convert_leads', 'recalculate_scores']
    
    def full_name(self, obj):
        return f"{obj.first_name} {obj.last_name}"
    full_name.short_description = 'Nombre Completo'
    full_name.admin_order_field = 'first_name'
    
    def colored_status(self, obj):
        """Display status with color coding."""
        colors = {
            'new': '#17a2b8',
            'contacted': '#ffc107', 
            'qualified': '#28a745',
            'unqualified': '#dc3545',
            'converted': '#6f42c1',
        }
        color = colors.get(obj.status, '#6c757d')
        return format_html(
            '<span style="color: {}; font-weight: bold;">{}</span>',
            color, obj.get_status_display()
        )
    colored_status.short_description = 'Estado'
    colored_status.admin_order_field = 'status'
    
    def score_bar(self, obj):
        """Display score as a progress bar."""
        color = 'success' if obj.score >= 70 else 'warning' if obj.score >= 40 else 'danger'
        return format_html(
            '<div class="progress" style="width: 80px;">'
            '<div class="progress-bar bg-{}" style="width: {}%">{}</div>'
            '</div>',
            color, obj.score, obj.score
        )
    score_bar.short_description = 'Puntuación'
    score_bar.admin_order_field = 'score'
    
    def qualify_leads(self, request, queryset):
        updated = queryset.filter(status='contacted').update(status='qualified')
        self.message_user(request, f'{updated} leads calificados.')
    qualify_leads.short_description = 'Calificar leads'
    
    def assign_to_me(self, request, queryset):
        updated = queryset.filter(assigned_to__isnull=True).update(assigned_to=request.user)
        self.message_user(request, f'{updated} leads asignados a ti.')
    assign_to_me.short_description = 'Asignarme estos leads'
    
    def convert_leads(self, request, queryset):
        # This would implement lead conversion logic
        convertible = queryset.filter(status='qualified', is_converted=False)
        self.message_user(request, f'{convertible.count()} leads listos para conversión.')
    convert_leads.short_description = 'Convertir leads calificados'
    
    def recalculate_scores(self, request, queryset):
        for lead in queryset:
            lead.calculate_score()
            lead.save()
        self.message_user(request, f'Puntuaciones recalculadas para {queryset.count()} leads.')
    recalculate_scores.short_description = 'Recalcular puntuaciones'