from django.contrib import admin
from django.utils.html import format_html
from django.urls import reverse
from django.utils.safestring import mark_safe
from django.utils import timezone
from django.db.models import Avg, Count, Sum
from .models import Report, ReportExecution, Dashboard, DashboardWidget, SalesReport, MarketingReport, SupportReport


class ReportExecutionInline(admin.TabularInline):
    model = ReportExecution
    extra = 0
    fields = ['executed_by', 'execution_time', 'rows_returned', 'status', 'executed_at']
    readonly_fields = ['executed_at']
    show_change_link = True


class DashboardWidgetInline(admin.TabularInline):
    model = DashboardWidget
    extra = 0
    fields = ['widget_type', 'title', 'position_x', 'position_y', 'width', 'height']
    show_change_link = True


@admin.register(Report)
class ReportAdmin(admin.ModelAdmin):
    list_display = [
        'name', 'report_type', 'format_type', 'is_public', 'execution_time_display',
        'last_executed', 'created_by', 'created_at'
    ]
    list_filter = [
        'report_type', 'format_type', 'is_public', 'date_range_required',
        'created_by', 'created_at', 'last_executed'
    ]
    search_fields = ['name', 'description', 'query_sql']
    readonly_fields = [
        'execution_time_avg', 'last_executed', 'created_by', 'created_at', 'updated_at'
    ]
    filter_horizontal = ['allowed_users']
    date_hierarchy = 'created_at'
    inlines = [ReportExecutionInline]
    
    fieldsets = (
        ('Información Básica', {
            'fields': ('name', 'description', 'report_type', 'format_type')
        }),
        ('Configuración del Reporte', {
            'fields': ('query_sql', 'filters', 'columns')
        }),
        ('Parámetros', {
            'fields': ('parameters', 'date_range_required'),
            'classes': ('collapse',)
        }),
        ('Control de Acceso', {
            'fields': ('is_public', 'allowed_users')
        }),
        ('Rendimiento', {
            'fields': ('execution_time_avg', 'last_executed')
        }),
        ('Seguimiento', {
            'fields': ('created_by', 'created_at', 'updated_at'),
            'classes': ('collapse',)
        }),
    )
    
    actions = ['execute_reports', 'make_public', 'make_private', 'duplicate_reports']
    
    def save_model(self, request, obj, form, change):
        if not change:
            obj.created_by = request.user
        super().save_model(request, obj, form, change)
    
    def execution_time_display(self, obj):
        """Display execution time with color coding."""
        time = obj.execution_time_avg
        if time == 0:
            return '-'
        
        color = '#28a745' if time <= 5 else '#ffc107' if time <= 30 else '#dc3545'
        return format_html(
            '<span style="color: {};">{:.2f}s</span>',
            color, time
        )
    execution_time_display.short_description = 'Tiempo Promedio'
    execution_time_display.admin_order_field = 'execution_time_avg'
    
    def execute_reports(self, request, queryset):
        # This would implement report execution
        self.message_user(request, f'Ejecutando {queryset.count()} reportes...')
    execute_reports.short_description = 'Ejecutar reportes'
    
    def make_public(self, request, queryset):
        updated = queryset.update(is_public=True)
        self.message_user(request, f'{updated} reportes hechos públicos.')
    make_public.short_description = 'Hacer públicos'
    
    def make_private(self, request, queryset):
        updated = queryset.update(is_public=False)
        self.message_user(request, f'{updated} reportes hechos privados.')
    make_private.short_description = 'Hacer privados'
    
    def duplicate_reports(self, request, queryset):
        for report in queryset:
            report.pk = None
            report.name = f"Copia de {report.name}"
            report.is_public = False
            report.execution_time_avg = 0
            report.last_executed = None
            report.save()
        self.message_user(request, f'{queryset.count()} reportes duplicados.')
    duplicate_reports.short_description = 'Duplicar reportes'


@admin.register(ReportExecution)
class ReportExecutionAdmin(admin.ModelAdmin):
    list_display = [
        'report', 'executed_by', 'execution_time_display', 'rows_returned',
        'status_display', 'exported_format', 'executed_at'
    ]
    list_filter = [
        'status', 'exported_format', 'executed_at', 'executed_by'
    ]
    search_fields = [
        'report__name', 'executed_by__first_name', 'executed_by__last_name',
        'error_message'
    ]
    readonly_fields = ['executed_at']
    raw_id_fields = ['report', 'executed_by']
    date_hierarchy = 'executed_at'
    
    fieldsets = (
        ('Información de Ejecución', {
            'fields': ('report', 'executed_by', 'executed_at')
        }),
        ('Parámetros Utilizados', {
            'fields': ('parameters_used',),
            'classes': ('collapse',)
        }),
        ('Rendimiento', {
            'fields': ('execution_time', 'rows_returned')
        }),
        ('Estado', {
            'fields': ('status', 'error_message')
        }),
        ('Exportación', {
            'fields': ('exported_format',)
        }),
    )
    
    def execution_time_display(self, obj):
        """Display execution time with color coding."""
        time = obj.execution_time
        color = '#28a745' if time <= 5 else '#ffc107' if time <= 30 else '#dc3545'
        return format_html(
            '<span style="color: {};">{:.2f}s</span>',
            color, time
        )
    execution_time_display.short_description = 'Tiempo'
    execution_time_display.admin_order_field = 'execution_time'
    
    def status_display(self, obj):
        """Display status with color coding."""
        colors = {
            'success': '#28a745',
            'error': '#dc3545',
            'timeout': '#fd7e14',
        }
        color = colors.get(obj.status, '#6c757d')
        return format_html(
            '<span style="color: {}; font-weight: bold;">{}</span>',
            color, obj.get_status_display()
        )
    status_display.short_description = 'Estado'
    status_display.admin_order_field = 'status'
    
    def has_add_permission(self, request):
        # Executions should be created automatically
        return False


@admin.register(Dashboard)
class DashboardAdmin(admin.ModelAdmin):
    list_display = [
        'name', 'is_default', 'is_public', 'widget_count', 'refresh_interval',
        'created_by', 'created_at'
    ]
    list_filter = [
        'is_default', 'is_public', 'refresh_interval', 'created_by', 'created_at'
    ]
    search_fields = ['name', 'description']
    readonly_fields = ['created_by', 'created_at', 'updated_at']
    filter_horizontal = ['allowed_users']
    inlines = [DashboardWidgetInline]
    
    fieldsets = (
        ('Información Básica', {
            'fields': ('name', 'description')
        }),
        ('Configuración', {
            'fields': ('layout', 'refresh_interval')
        }),
        ('Control de Acceso', {
            'fields': ('is_default', 'is_public', 'allowed_users')
        }),
        ('Seguimiento', {
            'fields': ('created_by', 'created_at', 'updated_at'),
            'classes': ('collapse',)
        }),
    )
    
    actions = ['set_as_default', 'make_public', 'make_private', 'duplicate_dashboards']
    
    def save_model(self, request, obj, form, change):
        if not change:
            obj.created_by = request.user
        super().save_model(request, obj, form, change)
    
    def widget_count(self, obj):
        """Count of widgets in the dashboard."""
        return obj.widgets.count()
    widget_count.short_description = 'Widgets'
    
    def set_as_default(self, request, queryset):
        # First, remove default from all dashboards
        Dashboard.objects.update(is_default=False)
        # Then set the selected one as default (only one can be default)
        if queryset.count() == 1:
            queryset.update(is_default=True)
            self.message_user(request, f'Dashboard establecido como predeterminado.')
        else:
            self.message_user(request, 'Solo se puede establecer un dashboard como predeterminado.', level='error')
    set_as_default.short_description = 'Establecer como predeterminado'
    
    def make_public(self, request, queryset):
        updated = queryset.update(is_public=True)
        self.message_user(request, f'{updated} dashboards hechos públicos.')
    make_public.short_description = 'Hacer públicos'
    
    def make_private(self, request, queryset):
        updated = queryset.update(is_public=False)
        self.message_user(request, f'{updated} dashboards hechos privados.')
    make_private.short_description = 'Hacer privados'
    
    def duplicate_dashboards(self, request, queryset):
        for dashboard in queryset:
            # Store widgets before duplicating dashboard
            widgets = list(dashboard.widgets.all())
            dashboard.pk = None
            dashboard.name = f"Copia de {dashboard.name}"
            dashboard.is_default = False
            dashboard.is_public = False
            dashboard.save()
            
            # Duplicate widgets
            for widget in widgets:
                widget.pk = None
                widget.dashboard = dashboard
                widget.save()
        
        self.message_user(request, f'{queryset.count()} dashboards duplicados.')
    duplicate_dashboards.short_description = 'Duplicar dashboards'


@admin.register(DashboardWidget)
class DashboardWidgetAdmin(admin.ModelAdmin):
    list_display = [
        'title', 'dashboard', 'widget_type', 'report', 'position_display',
        'size_display', 'cache_status'
    ]
    list_filter = [
        'widget_type', 'dashboard', 'dashboard__created_by', 'created_at'
    ]
    search_fields = ['title', 'dashboard__name', 'report__name']
    readonly_fields = ['cached_data', 'cache_expires', 'created_at', 'updated_at']
    raw_id_fields = ['dashboard', 'report']
    
    fieldsets = (
        ('Información Básica', {
            'fields': ('dashboard', 'widget_type', 'title', 'report')
        }),
        ('Posición y Tamaño', {
            'fields': ('position_x', 'position_y', 'width', 'height')
        }),
        ('Configuración', {
            'fields': ('config',),
            'classes': ('collapse',)
        }),
        ('Cache de Datos', {
            'fields': ('cached_data', 'cache_expires'),
            'classes': ('collapse',)
        }),
        ('Seguimiento', {
            'fields': ('created_at', 'updated_at'),
            'classes': ('collapse',)
        }),
    )
    
    actions = ['clear_cache', 'refresh_data']
    
    def position_display(self, obj):
        """Display position coordinates."""
        return f"({obj.position_x}, {obj.position_y})"
    position_display.short_description = 'Posición'
    
    def size_display(self, obj):
        """Display widget size."""
        return f"{obj.width} × {obj.height}"
    size_display.short_description = 'Tamaño'
    
    def cache_status(self, obj):
        """Display cache status."""
        if obj.is_cache_valid():
            return format_html('<span style="color: #28a745;">Válido</span>')
        elif obj.cache_expires:
            return format_html('<span style="color: #dc3545;">Expirado</span>')
        else:
            return format_html('<span style="color: #6c757d;">Sin cache</span>')
    cache_status.short_description = 'Estado del Cache'
    
    def clear_cache(self, request, queryset):
        updated = queryset.update(cached_data={}, cache_expires=None)
        self.message_user(request, f'Cache limpiado para {updated} widgets.')
    clear_cache.short_description = 'Limpiar cache'
    
    def refresh_data(self, request, queryset):
        # This would implement data refresh logic
        self.message_user(request, f'Datos actualizados para {queryset.count()} widgets.')
    refresh_data.short_description = 'Actualizar datos'


@admin.register(SalesReport)
class SalesReportAdmin(admin.ModelAdmin):
    list_display = [
        'period_display', 'total_revenue', 'won_opportunities', 'win_rate_display',
        'avg_deal_size', 'pipeline_value', 'top_performer', 'generated_at'
    ]
    list_filter = [
        'period_type', 'start_date', 'end_date', 'generated_by', 'generated_at'
    ]
    search_fields = ['top_performer__first_name', 'top_performer__last_name']
    readonly_fields = ['generated_by', 'generated_at']
    raw_id_fields = ['top_performer', 'generated_by']
    date_hierarchy = 'generated_at'
    
    fieldsets = (
        ('Período del Reporte', {
            'fields': ('period_type', 'start_date', 'end_date')
        }),
        ('Métricas de Ventas', {
            'fields': (
                'total_opportunities', 'total_revenue', 'won_opportunities', 'won_revenue',
                'lost_opportunities', 'lost_revenue'
            )
        }),
        ('Tasas de Conversión', {
            'fields': ('win_rate', 'avg_deal_size', 'avg_sales_cycle')
        }),
        ('Pipeline', {
            'fields': ('pipeline_value', 'forecast_revenue')
        }),
        ('Rendimiento del Equipo', {
            'fields': ('top_performer', 'sales_rep_count')
        }),
        ('Seguimiento', {
            'fields': ('generated_by', 'generated_at'),
            'classes': ('collapse',)
        }),
    )
    
    actions = ['regenerate_reports', 'export_to_excel']
    
    def period_display(self, obj):
        """Display period information."""
        return f"{obj.get_period_type_display()}: {obj.start_date} - {obj.end_date}"
    period_display.short_description = 'Período'
    
    def win_rate_display(self, obj):
        """Display win rate with color coding."""
        rate = obj.win_rate
        color = '#28a745' if rate >= 30 else '#ffc107' if rate >= 15 else '#dc3545'
        return format_html(
            '<span style="color: {};">{:.1f}%</span>',
            color, rate
        )
    win_rate_display.short_description = 'Tasa de Ganancia'
    win_rate_display.admin_order_field = 'win_rate'
    
    def regenerate_reports(self, request, queryset):
        # This would implement report regeneration logic
        self.message_user(request, f'Regenerando {queryset.count()} reportes de ventas...')
    regenerate_reports.short_description = 'Regenerar reportes'
    
    def export_to_excel(self, request, queryset):
        # This would implement Excel export
        self.message_user(request, f'Exportando {queryset.count()} reportes a Excel...')
    export_to_excel.short_description = 'Exportar a Excel'


@admin.register(MarketingReport)
class MarketingReportAdmin(admin.ModelAdmin):
    list_display = [
        'campaign', 'report_date', 'total_leads', 'conversion_rate_display',
        'email_open_rate_display', 'roi_display', 'cost_per_lead', 'generated_at'
    ]
    list_filter = [
        'campaign__campaign_type', 'campaign__status', 'report_date',
        'generated_by', 'generated_at'
    ]
    search_fields = ['campaign__name', 'campaign__description']
    readonly_fields = ['generated_by', 'generated_at']
    raw_id_fields = ['campaign', 'generated_by']
    date_hierarchy = 'report_date'
    
    fieldsets = (
        ('Información de la Campaña', {
            'fields': ('campaign', 'report_date')
        }),
        ('Métricas de Leads', {
            'fields': ('total_leads', 'qualified_leads', 'converted_leads')
        }),
        ('Métricas de Email', {
            'fields': ('emails_sent', 'emails_opened', 'emails_clicked', 'unsubscribes')
        }),
        ('Tasas de Conversión', {
            'fields': ('lead_conversion_rate', 'email_open_rate', 'email_click_rate')
        }),
        ('Métricas de ROI', {
            'fields': ('total_cost', 'revenue_generated', 'roi_percentage', 'cost_per_lead')
        }),
        ('Seguimiento', {
            'fields': ('generated_by', 'generated_at'),
            'classes': ('collapse',)
        }),
    )
    
    actions = ['regenerate_reports', 'calculate_roi']
    
    def conversion_rate_display(self, obj):
        """Display conversion rate with color coding."""
        rate = obj.lead_conversion_rate
        color = '#28a745' if rate >= 5 else '#ffc107' if rate >= 2 else '#dc3545'
        return format_html(
            '<span style="color: {};">{:.1f}%</span>',
            color, rate
        )
    conversion_rate_display.short_description = 'Conversión'
    conversion_rate_display.admin_order_field = 'lead_conversion_rate'
    
    def email_open_rate_display(self, obj):
        """Display email open rate with color coding."""
        rate = obj.email_open_rate
        color = '#28a745' if rate >= 20 else '#ffc107' if rate >= 10 else '#dc3545'
        return format_html(
            '<span style="color: {};">{:.1f}%</span>',
            color, rate
        )
    email_open_rate_display.short_description = 'Apertura Email'
    email_open_rate_display.admin_order_field = 'email_open_rate'
    
    def roi_display(self, obj):
        """Display ROI with color coding."""
        roi = obj.roi_percentage
        color = '#28a745' if roi > 0 else '#dc3545' if roi < -50 else '#ffc107'
        return format_html(
            '<span style="color: {}; font-weight: bold;">{:.1f}%</span>',
            color, roi
        )
    roi_display.short_description = 'ROI'
    roi_display.admin_order_field = 'roi_percentage'
    
    def regenerate_reports(self, request, queryset):
        # This would implement report regeneration logic
        self.message_user(request, f'Regenerando {queryset.count()} reportes de marketing...')
    regenerate_reports.short_description = 'Regenerar reportes'
    
    def calculate_roi(self, request, queryset):
        # This would implement ROI recalculation
        self.message_user(request, f'Recalculando ROI para {queryset.count()} reportes...')
    calculate_roi.short_description = 'Recalcular ROI'


@admin.register(SupportReport)
class SupportReportAdmin(admin.ModelAdmin):
    list_display = [
        'period_display', 'total_tickets', 'resolved_tickets', 'sla_compliance',
        'avg_response_time_display', 'satisfaction_display', 'most_productive_agent', 'generated_at'
    ]
    list_filter = [
        'period_type', 'start_date', 'end_date', 'generated_by', 'generated_at'
    ]
    search_fields = [
        'most_productive_agent__first_name', 'most_productive_agent__last_name'
    ]
    readonly_fields = ['generated_by', 'generated_at']
    raw_id_fields = ['most_productive_agent', 'generated_by']
    date_hierarchy = 'generated_at'
    
    fieldsets = (
        ('Período del Reporte', {
            'fields': ('period_type', 'start_date', 'end_date')
        }),
        ('Métricas de Tickets', {
            'fields': (
                'total_tickets', 'new_tickets', 'resolved_tickets', 'closed_tickets'
            )
        }),
        ('Tiempos de Respuesta', {
            'fields': ('avg_first_response_time', 'avg_resolution_time')
        }),
        ('Métricas de SLA', {
            'fields': ('sla_met_percentage', 'overdue_tickets')
        }),
        ('Satisfacción', {
            'fields': ('avg_satisfaction_rating', 'satisfaction_responses')
        }),
        ('Rendimiento del Equipo', {
            'fields': ('total_agents', 'most_productive_agent')
        }),
        ('Seguimiento', {
            'fields': ('generated_by', 'generated_at'),
            'classes': ('collapse',)
        }),
    )
    
    actions = ['regenerate_reports', 'export_metrics']
    
    def period_display(self, obj):
        """Display period information."""
        return f"{obj.get_period_type_display()}: {obj.start_date} - {obj.end_date}"
    period_display.short_description = 'Período'
    
    def sla_compliance(self, obj):
        """Display SLA compliance with color coding."""
        rate = obj.sla_met_percentage
        color = '#28a745' if rate >= 95 else '#ffc107' if rate >= 80 else '#dc3545'
        return format_html(
            '<span style="color: {};">{:.1f}%</span>',
            color, rate
        )
    sla_compliance.short_description = 'Cumplimiento SLA'
    sla_compliance.admin_order_field = 'sla_met_percentage'
    
    def avg_response_time_display(self, obj):
        """Display average response time with color coding."""
        time = obj.avg_first_response_time
        color = '#28a745' if time <= 2 else '#ffc107' if time <= 8 else '#dc3545'
        return format_html(
            '<span style="color: {};">{:.1f}h</span>',
            color, time
        )
    avg_response_time_display.short_description = 'Tiempo Respuesta'
    avg_response_time_display.admin_order_field = 'avg_first_response_time'
    
    def satisfaction_display(self, obj):
        """Display satisfaction rating with stars."""
        if obj.avg_satisfaction_rating and obj.satisfaction_responses > 0:
            rating = obj.avg_satisfaction_rating
            stars = '★' * int(rating) + '☆' * (5 - int(rating))
            color = '#ffc107' if rating >= 4 else '#fd7e14' if rating >= 3 else '#dc3545'
            return format_html(
                '<span style="color: {};">{} ({:.1f})</span>',
                color, stars, rating
            )
        return '-'
    satisfaction_display.short_description = 'Satisfacción'
    
    def regenerate_reports(self, request, queryset):
        # This would implement report regeneration logic
        self.message_user(request, f'Regenerando {queryset.count()} reportes de soporte...')
    regenerate_reports.short_description = 'Regenerar reportes'
    
    def export_metrics(self, request, queryset):
        # This would implement metrics export
        self.message_user(request, f'Exportando métricas de {queryset.count()} reportes...')
    export_metrics.short_description = 'Exportar métricas'