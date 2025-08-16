from rest_framework import serializers
from .models import (
    Report, ReportExecution, Dashboard, DashboardWidget,
    SalesReport, MarketingReport, SupportReport
)
from django.contrib.auth import get_user_model

User = get_user_model()


class ReportSerializer(serializers.ModelSerializer):
    created_by_name = serializers.CharField(source='created_by.get_full_name', read_only=True)
    report_type_display = serializers.CharField(source='get_report_type_display', read_only=True)
    format_type_display = serializers.CharField(source='get_format_type_display', read_only=True)
    executions_count = serializers.IntegerField(source='executions.count', read_only=True)
    allowed_users_count = serializers.IntegerField(source='allowed_users.count', read_only=True)
    
    class Meta:
        model = Report
        fields = '__all__'
        read_only_fields = ['execution_time_avg', 'last_executed', 'created_at', 'updated_at']
    
    def validate_query_sql(self, value):
        """Basic SQL validation to prevent dangerous queries."""
        if value:
            dangerous_keywords = ['DROP', 'DELETE', 'TRUNCATE', 'ALTER', 'CREATE', 'INSERT', 'UPDATE']
            value_upper = value.upper()
            
            for keyword in dangerous_keywords:
                if keyword in value_upper:
                    raise serializers.ValidationError(
                        f"La consulta no puede contener la palabra clave '{keyword}'"
                    )
        
        return value


class ReportExecutionSerializer(serializers.ModelSerializer):
    report_name = serializers.CharField(source='report.name', read_only=True)
    executed_by_name = serializers.CharField(source='executed_by.get_full_name', read_only=True)
    status_display = serializers.CharField(source='get_status_display', read_only=True)
    exported_format_display = serializers.CharField(source='get_exported_format_display', read_only=True)
    
    class Meta:
        model = ReportExecution
        fields = '__all__'
        read_only_fields = ['executed_at']


class DashboardSerializer(serializers.ModelSerializer):
    created_by_name = serializers.CharField(source='created_by.get_full_name', read_only=True)
    widgets_count = serializers.IntegerField(source='widgets.count', read_only=True)
    allowed_users_count = serializers.IntegerField(source='allowed_users.count', read_only=True)
    
    class Meta:
        model = Dashboard
        fields = '__all__'
        read_only_fields = ['created_at', 'updated_at']
    
    def validate_layout(self, value):
        """Validate layout configuration."""
        if not isinstance(value, dict):
            raise serializers.ValidationError("El layout debe ser un objeto JSON")
        
        # Basic layout validation
        if 'columns' in value:
            if not isinstance(value['columns'], int) or value['columns'] < 1 or value['columns'] > 24:
                raise serializers.ValidationError("Las columnas deben estar entre 1 y 24")
        
        return value


class DashboardWidgetSerializer(serializers.ModelSerializer):
    dashboard_name = serializers.CharField(source='dashboard.name', read_only=True)
    report_name = serializers.CharField(source='report.name', read_only=True)
    widget_type_display = serializers.CharField(source='get_widget_type_display', read_only=True)
    is_cache_valid = serializers.ReadOnlyField()
    
    class Meta:
        model = DashboardWidget
        fields = '__all__'
        read_only_fields = ['cached_data', 'cache_expires', 'created_at', 'updated_at']
    
    def validate(self, attrs):
        """Validate widget configuration."""
        # Ensure widget has either a report or is a text widget
        widget_type = attrs.get('widget_type', self.instance.widget_type if self.instance else None)
        report = attrs.get('report', self.instance.report if self.instance else None)
        
        if widget_type != 'text' and not report:
            raise serializers.ValidationError({
                'report': 'Se requiere un reporte para este tipo de widget'
            })
        
        # Validate position and size
        position_x = attrs.get('position_x', 0)
        position_y = attrs.get('position_y', 0)
        width = attrs.get('width', 4)
        height = attrs.get('height', 4)
        
        if position_x < 0 or position_y < 0:
            raise serializers.ValidationError("La posición no puede ser negativa")
        
        if width < 1 or width > 24 or height < 1:
            raise serializers.ValidationError("El tamaño del widget no es válido")
        
        return attrs


class SalesReportSerializer(serializers.ModelSerializer):
    generated_by_name = serializers.CharField(source='generated_by.get_full_name', read_only=True)
    top_performer_name = serializers.CharField(source='top_performer.get_full_name', read_only=True)
    period_type_display = serializers.CharField(source='get_period_type_display', read_only=True)
    duration_days = serializers.SerializerMethodField()
    
    class Meta:
        model = SalesReport
        fields = '__all__'
        read_only_fields = ['generated_at']
    
    def get_duration_days(self, obj):
        return (obj.end_date - obj.start_date).days + 1


class MarketingReportSerializer(serializers.ModelSerializer):
    campaign_name = serializers.CharField(source='campaign.name', read_only=True)
    campaign_type = serializers.CharField(source='campaign.get_campaign_type_display', read_only=True)
    generated_by_name = serializers.CharField(source='generated_by.get_full_name', read_only=True)
    
    class Meta:
        model = MarketingReport
        fields = '__all__'
        read_only_fields = ['generated_at']
    
    def validate(self, attrs):
        """Calculate derived metrics."""
        total_leads = attrs.get('total_leads', self.instance.total_leads if self.instance else 0)
        qualified_leads = attrs.get('qualified_leads', self.instance.qualified_leads if self.instance else 0)
        converted_leads = attrs.get('converted_leads', self.instance.converted_leads if self.instance else 0)
        
        # Validate lead progression
        if qualified_leads > total_leads:
            raise serializers.ValidationError({
                'qualified_leads': 'Los leads calificados no pueden exceder el total de leads'
            })
        
        if converted_leads > qualified_leads:
            raise serializers.ValidationError({
                'converted_leads': 'Los leads convertidos no pueden exceder los leads calificados'
            })
        
        # Calculate conversion rate
        if total_leads > 0:
            attrs['lead_conversion_rate'] = (converted_leads / total_leads) * 100
        
        return attrs


class SupportReportSerializer(serializers.ModelSerializer):
    generated_by_name = serializers.CharField(source='generated_by.get_full_name', read_only=True)
    most_productive_agent_name = serializers.CharField(
        source='most_productive_agent.get_full_name',
        read_only=True
    )
    period_type_display = serializers.CharField(source='get_period_type_display', read_only=True)
    resolution_rate = serializers.SerializerMethodField()
    
    class Meta:
        model = SupportReport
        fields = '__all__'
        read_only_fields = ['generated_at']
    
    def get_resolution_rate(self, obj):
        if obj.total_tickets > 0:
            return round((obj.resolved_tickets / obj.total_tickets) * 100, 2)
        return 0
    
    def validate(self, attrs):
        """Validate ticket counts."""
        total_tickets = attrs.get('total_tickets', self.instance.total_tickets if self.instance else 0)
        resolved_tickets = attrs.get('resolved_tickets', self.instance.resolved_tickets if self.instance else 0)
        closed_tickets = attrs.get('closed_tickets', self.instance.closed_tickets if self.instance else 0)
        
        if resolved_tickets > total_tickets:
            raise serializers.ValidationError({
                'resolved_tickets': 'Los tickets resueltos no pueden exceder el total'
            })
        
        if closed_tickets > total_tickets:
            raise serializers.ValidationError({
                'closed_tickets': 'Los tickets cerrados no pueden exceder el total'
            })
        
        return attrs