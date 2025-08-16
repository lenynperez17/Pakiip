from django.contrib import admin
from django.utils.html import format_html
from .models import Account, Contact, Activity


@admin.register(Account)
class AccountAdmin(admin.ModelAdmin):
    list_display = [
        'name', 'account_type', 'industry', 'ruc', 'billing_city',
        'contact_count', 'assigned_to', 'created_at'
    ]
    list_filter = ['account_type', 'industry', 'billing_city', 'created_at']
    search_fields = ['name', 'ruc', 'website']
    readonly_fields = ['created_by', 'created_at', 'updated_at']
    
    fieldsets = (
        ('Información Básica', {
            'fields': ('name', 'account_type', 'industry', 'annual_revenue', 'employees', 'website')
        }),
        ('Identificación Fiscal', {
            'fields': ('ruc',)
        }),
        ('Dirección de Facturación', {
            'fields': ('billing_street', 'billing_city', 'billing_state', 'billing_postal_code', 'billing_country')
        }),
        ('Asignación', {
            'fields': ('assigned_to',)
        }),
        ('Seguimiento', {
            'fields': ('created_by', 'created_at', 'updated_at'),
            'classes': ('collapse',)
        }),
    )
    
    def contact_count(self, obj):
        count = obj.contacts.count()
        return format_html(
            '<span style="color: {};">{}</span>',
            'green' if count > 0 else 'gray',
            count
        )
    contact_count.short_description = 'Contactos'
    
    def save_model(self, request, obj, form, change):
        if not change:
            obj.created_by = request.user
        super().save_model(request, obj, form, change)


@admin.register(Contact)
class ContactAdmin(admin.ModelAdmin):
    list_display = [
        'full_name', 'email', 'phone', 'job_title', 'account',
        'assigned_to', 'last_activity_date', 'created_at'
    ]
    list_filter = [
        'job_title', 'department', 'preferred_contact_method',
        'do_not_call', 'do_not_email', 'created_at'
    ]
    search_fields = ['first_name', 'last_name', 'email', 'phone', 'dni', 'account__name']
    readonly_fields = ['created_by', 'created_at', 'updated_at', 'last_activity_date']
    raw_id_fields = ['account', 'reports_to']
    
    fieldsets = (
        ('Información Personal', {
            'fields': ('salutation', 'first_name', 'last_name', 'dni')
        }),
        ('Información Profesional', {
            'fields': ('job_title', 'department', 'account', 'reports_to')
        }),
        ('Información de Contacto', {
            'fields': ('email', 'phone', 'mobile', 'preferred_contact_method')
        }),
        ('Dirección', {
            'fields': ('street', 'city', 'state', 'postal_code', 'country'),
            'classes': ('collapse',)
        }),
        ('Preferencias de Comunicación', {
            'fields': ('do_not_call', 'do_not_email')
        }),
        ('Asignación', {
            'fields': ('assigned_to',)
        }),
        ('Seguimiento', {
            'fields': ('created_by', 'created_at', 'updated_at', 'last_activity_date'),
            'classes': ('collapse',)
        }),
    )
    
    def save_model(self, request, obj, form, change):
        if not change:
            obj.created_by = request.user
        super().save_model(request, obj, form, change)


@admin.register(Activity)
class ActivityAdmin(admin.ModelAdmin):
    list_display = [
        'subject', 'activity_type', 'status', 'assigned_to',
        'contact', 'account', 'due_date', 'is_overdue'
    ]
    list_filter = ['activity_type', 'status', 'assigned_to', 'due_date', 'created_at']
    search_fields = ['subject', 'description', 'contact__first_name', 'contact__last_name']
    readonly_fields = ['created_by', 'created_at', 'updated_at', 'completed_date']
    raw_id_fields = ['contact', 'account']
    date_hierarchy = 'due_date'
    
    fieldsets = (
        ('Información de la Actividad', {
            'fields': ('activity_type', 'subject', 'description')
        }),
        ('Entidades Relacionadas', {
            'fields': ('contact', 'account')
        }),
        ('Programación', {
            'fields': ('due_date', 'duration', 'status', 'completed_date')
        }),
        ('Asignación', {
            'fields': ('assigned_to',)
        }),
        ('Seguimiento', {
            'fields': ('created_by', 'created_at', 'updated_at'),
            'classes': ('collapse',)
        }),
    )
    
    def is_overdue(self, obj):
        from django.utils import timezone
        if obj.status != 'completed' and obj.due_date:
            is_overdue = timezone.now() > obj.due_date
            return format_html(
                '<span style="color: {};">{}</span>',
                'red' if is_overdue else 'green',
                'Sí' if is_overdue else 'No'
            )
        return '-'
    is_overdue.short_description = 'Vencido'
    
    def save_model(self, request, obj, form, change):
        if not change:
            obj.created_by = request.user
        super().save_model(request, obj, form, change)
    
    actions = ['mark_as_completed']
    
    def mark_as_completed(self, request, queryset):
        from django.utils import timezone
        updated = queryset.filter(status__in=['planned', 'pending']).update(
            status='completed',
            completed_date=timezone.now()
        )
        self.message_user(request, f'{updated} actividades marcadas como completadas.')
    mark_as_completed.short_description = 'Marcar como completadas'