from django.db import models
from django.contrib.auth import get_user_model
from django.utils import timezone
import json

User = get_user_model()


class Report(models.Model):
    """Custom reports and dashboards."""
    REPORT_TYPE_CHOICES = [
        ('sales', 'Ventas'),
        ('marketing', 'Marketing'),
        ('support', 'Soporte'),
        ('contacts', 'Contactos'),
        ('activities', 'Actividades'),
        ('custom', 'Personalizado'),
    ]
    
    FORMAT_CHOICES = [
        ('table', 'Tabla'),
        ('chart_bar', 'Gráfico de Barras'),
        ('chart_line', 'Gráfico de Líneas'),
        ('chart_pie', 'Gráfico Circular'),
        ('dashboard', 'Dashboard'),
    ]
    
    # Basic information
    name = models.CharField(max_length=255, verbose_name="Nombre del reporte")
    description = models.TextField(blank=True, verbose_name="Descripción")
    report_type = models.CharField(max_length=20, choices=REPORT_TYPE_CHOICES)
    format_type = models.CharField(max_length=20, choices=FORMAT_CHOICES, default='table')
    
    # Report configuration
    query_sql = models.TextField(blank=True, verbose_name="Consulta SQL")
    filters = models.JSONField(default=dict, blank=True, verbose_name="Filtros")
    columns = models.JSONField(default=list, blank=True, verbose_name="Columnas")
    
    # Parameters
    parameters = models.JSONField(default=dict, blank=True, verbose_name="Parámetros")
    date_range_required = models.BooleanField(default=True, verbose_name="Requiere rango de fechas")
    
    # Access control
    is_public = models.BooleanField(default=False, verbose_name="Público")
    allowed_users = models.ManyToManyField(User, blank=True, related_name='allowed_reports')
    
    # Performance
    execution_time_avg = models.FloatField(default=0, verbose_name="Tiempo promedio de ejecución (seg)")
    last_executed = models.DateTimeField(null=True, blank=True)
    
    # Tracking
    created_by = models.ForeignKey(User, on_delete=models.SET_NULL, null=True, related_name='created_reports')
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)
    
    class Meta:
        verbose_name = "Reporte"
        verbose_name_plural = "Reportes"
        ordering = ['-created_at']
    
    def __str__(self):
        return f"{self.name} ({self.get_report_type_display()})"


class ReportExecution(models.Model):
    """Track report executions."""
    report = models.ForeignKey(Report, on_delete=models.CASCADE, related_name='executions')
    executed_by = models.ForeignKey(User, on_delete=models.SET_NULL, null=True)
    
    # Execution details
    parameters_used = models.JSONField(default=dict, verbose_name="Parámetros utilizados")
    execution_time = models.FloatField(verbose_name="Tiempo de ejecución (seg)")
    rows_returned = models.IntegerField(default=0, verbose_name="Filas devueltas")
    
    # Status
    status = models.CharField(
        max_length=20,
        choices=[('success', 'Exitoso'), ('error', 'Error'), ('timeout', 'Timeout')],
        default='success'
    )
    error_message = models.TextField(blank=True, verbose_name="Mensaje de error")
    
    # Export
    exported_format = models.CharField(
        max_length=10,
        choices=[('pdf', 'PDF'), ('excel', 'Excel'), ('csv', 'CSV'), ('none', 'Sin exportar')],
        default='none'
    )
    
    # Tracking
    executed_at = models.DateTimeField(auto_now_add=True)
    
    class Meta:
        verbose_name = "Ejecución de Reporte"
        verbose_name_plural = "Ejecuciones de Reportes"
        ordering = ['-executed_at']
    
    def __str__(self):
        return f"{self.report.name} - {self.executed_at}"


class Dashboard(models.Model):
    """Custom dashboards with multiple widgets."""
    name = models.CharField(max_length=255, verbose_name="Nombre del dashboard")
    description = models.TextField(blank=True, verbose_name="Descripción")
    
    # Layout configuration
    layout = models.JSONField(default=dict, verbose_name="Configuración de diseño")
    refresh_interval = models.IntegerField(default=300, verbose_name="Intervalo de actualización (seg)")
    
    # Access control
    is_default = models.BooleanField(default=False, verbose_name="Dashboard por defecto")
    is_public = models.BooleanField(default=False, verbose_name="Público")
    allowed_users = models.ManyToManyField(User, blank=True, related_name='allowed_dashboards')
    
    # Tracking
    created_by = models.ForeignKey(User, on_delete=models.SET_NULL, null=True, related_name='created_dashboards')
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)
    
    class Meta:
        verbose_name = "Dashboard"
        verbose_name_plural = "Dashboards"
        ordering = ['-created_at']
    
    def __str__(self):
        return self.name


class DashboardWidget(models.Model):
    """Individual widgets within a dashboard."""
    WIDGET_TYPE_CHOICES = [
        ('metric', 'Métrica'),
        ('chart_bar', 'Gráfico de Barras'),
        ('chart_line', 'Gráfico de Líneas'),
        ('chart_pie', 'Gráfico Circular'),
        ('table', 'Tabla'),
        ('gauge', 'Medidor'),
        ('text', 'Texto'),
    ]
    
    dashboard = models.ForeignKey(Dashboard, on_delete=models.CASCADE, related_name='widgets')
    report = models.ForeignKey(Report, on_delete=models.CASCADE, null=True, blank=True)
    
    # Widget configuration
    widget_type = models.CharField(max_length=20, choices=WIDGET_TYPE_CHOICES)
    title = models.CharField(max_length=255, verbose_name="Título")
    
    # Layout
    position_x = models.IntegerField(default=0)
    position_y = models.IntegerField(default=0)
    width = models.IntegerField(default=4)
    height = models.IntegerField(default=4)
    
    # Configuration
    config = models.JSONField(default=dict, verbose_name="Configuración del widget")
    
    # Data caching
    cached_data = models.JSONField(default=dict, blank=True)
    cache_expires = models.DateTimeField(null=True, blank=True)
    
    # Tracking
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)
    
    class Meta:
        verbose_name = "Widget de Dashboard"
        verbose_name_plural = "Widgets de Dashboard"
        ordering = ['position_y', 'position_x']
    
    def __str__(self):
        return f"{self.title} ({self.dashboard.name})"
    
    def is_cache_valid(self):
        """Check if cached data is still valid."""
        if self.cache_expires:
            return timezone.now() < self.cache_expires
        return False


class SalesReport(models.Model):
    """Predefined sales reports with specific metrics."""
    PERIOD_CHOICES = [
        ('daily', 'Diario'),
        ('weekly', 'Semanal'),
        ('monthly', 'Mensual'),
        ('quarterly', 'Trimestral'),
        ('yearly', 'Anual'),
    ]
    
    # Report period
    period_type = models.CharField(max_length=10, choices=PERIOD_CHOICES)
    start_date = models.DateField()
    end_date = models.DateField()
    
    # Sales metrics
    total_opportunities = models.IntegerField(default=0)
    total_revenue = models.DecimalField(max_digits=12, decimal_places=2, default=0)
    won_opportunities = models.IntegerField(default=0)
    won_revenue = models.DecimalField(max_digits=12, decimal_places=2, default=0)
    lost_opportunities = models.IntegerField(default=0)
    lost_revenue = models.DecimalField(max_digits=12, decimal_places=2, default=0)
    
    # Conversion rates
    win_rate = models.DecimalField(max_digits=5, decimal_places=2, default=0)  # Percentage
    avg_deal_size = models.DecimalField(max_digits=12, decimal_places=2, default=0)
    avg_sales_cycle = models.IntegerField(default=0)  # Days
    
    # Pipeline metrics
    pipeline_value = models.DecimalField(max_digits=12, decimal_places=2, default=0)
    forecast_revenue = models.DecimalField(max_digits=12, decimal_places=2, default=0)
    
    # Team performance
    top_performer = models.ForeignKey(User, on_delete=models.SET_NULL, null=True, blank=True, related_name='top_sales_reports')
    sales_rep_count = models.IntegerField(default=0)
    
    # Tracking
    generated_by = models.ForeignKey(User, on_delete=models.SET_NULL, null=True, related_name='generated_sales_reports')
    generated_at = models.DateTimeField(auto_now_add=True)
    
    class Meta:
        verbose_name = "Reporte de Ventas"
        verbose_name_plural = "Reportes de Ventas"
        ordering = ['-generated_at']
        unique_together = ['period_type', 'start_date', 'end_date']
    
    def __str__(self):
        return f"Reporte de Ventas {self.get_period_type_display()} - {self.start_date} a {self.end_date}"


class MarketingReport(models.Model):
    """Predefined marketing reports with campaign metrics."""
    campaign = models.ForeignKey('marketing.Campaign', on_delete=models.CASCADE, related_name='reports')
    
    # Campaign metrics
    total_leads = models.IntegerField(default=0)
    qualified_leads = models.IntegerField(default=0)
    converted_leads = models.IntegerField(default=0)
    
    # Email metrics (if applicable)
    emails_sent = models.IntegerField(default=0)
    emails_opened = models.IntegerField(default=0)
    emails_clicked = models.IntegerField(default=0)
    unsubscribes = models.IntegerField(default=0)
    
    # Conversion rates
    lead_conversion_rate = models.DecimalField(max_digits=5, decimal_places=2, default=0)
    email_open_rate = models.DecimalField(max_digits=5, decimal_places=2, default=0)
    email_click_rate = models.DecimalField(max_digits=5, decimal_places=2, default=0)
    
    # ROI metrics
    total_cost = models.DecimalField(max_digits=10, decimal_places=2, default=0)
    revenue_generated = models.DecimalField(max_digits=12, decimal_places=2, default=0)
    roi_percentage = models.DecimalField(max_digits=8, decimal_places=2, default=0)
    cost_per_lead = models.DecimalField(max_digits=8, decimal_places=2, default=0)
    
    # Tracking
    report_date = models.DateField(default=timezone.now)
    generated_by = models.ForeignKey(User, on_delete=models.SET_NULL, null=True, related_name='generated_marketing_reports')
    generated_at = models.DateTimeField(auto_now_add=True)
    
    class Meta:
        verbose_name = "Reporte de Marketing"
        verbose_name_plural = "Reportes de Marketing"
        ordering = ['-generated_at']
    
    def __str__(self):
        return f"Reporte Marketing - {self.campaign.name} ({self.report_date})"


class SupportReport(models.Model):
    """Support/ticket metrics reports."""
    PERIOD_CHOICES = [
        ('daily', 'Diario'),
        ('weekly', 'Semanal'),
        ('monthly', 'Mensual'),
        ('quarterly', 'Trimestral'),
    ]
    
    # Report period
    period_type = models.CharField(max_length=10, choices=PERIOD_CHOICES)
    start_date = models.DateField()
    end_date = models.DateField()
    
    # Ticket metrics
    total_tickets = models.IntegerField(default=0)
    new_tickets = models.IntegerField(default=0)
    resolved_tickets = models.IntegerField(default=0)
    closed_tickets = models.IntegerField(default=0)
    
    # Response times (in hours)
    avg_first_response_time = models.DecimalField(max_digits=8, decimal_places=2, default=0)
    avg_resolution_time = models.DecimalField(max_digits=8, decimal_places=2, default=0)
    
    # SLA metrics
    sla_met_percentage = models.DecimalField(max_digits=5, decimal_places=2, default=0)
    overdue_tickets = models.IntegerField(default=0)
    
    # Satisfaction
    avg_satisfaction_rating = models.DecimalField(max_digits=3, decimal_places=2, default=0)
    satisfaction_responses = models.IntegerField(default=0)
    
    # Team performance
    total_agents = models.IntegerField(default=0)
    most_productive_agent = models.ForeignKey(User, on_delete=models.SET_NULL, null=True, blank=True)
    
    # Tracking
    generated_by = models.ForeignKey(
        User, 
        on_delete=models.CASCADE,
        related_name='generated_support_reports'
    )
    generated_at = models.DateTimeField(auto_now_add=True)
    
    class Meta:
        verbose_name = "Reporte de Soporte"
        verbose_name_plural = "Reportes de Soporte"
        ordering = ['-generated_at']
        unique_together = ['period_type', 'start_date', 'end_date']
    
    def __str__(self):
        return f"Reporte de Soporte {self.get_period_type_display()} - {self.start_date} a {self.end_date}"