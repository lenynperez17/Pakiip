from django.contrib import admin
from django.utils.html import format_html
from django.urls import reverse
from django.utils.safestring import mark_safe
from .models import Opportunity, OpportunityProduct, Quote, Commission


class OpportunityProductInline(admin.TabularInline):
    model = OpportunityProduct
    extra = 1
    fields = ['product_name', 'product_code', 'quantity', 'unit_price', 'discount', 'total_price']
    readonly_fields = ['total_price']


class QuoteInline(admin.TabularInline):
    model = Quote
    extra = 0
    fields = ['quote_number', 'subject', 'status', 'total', 'issue_date', 'expiration_date']
    readonly_fields = ['quote_number', 'total']
    show_change_link = True


class CommissionInline(admin.TabularInline):
    model = Commission
    extra = 0
    fields = ['sales_rep', 'sale_amount', 'commission_rate', 'commission_amount', 'is_paid']
    readonly_fields = ['commission_amount']
    show_change_link = True


@admin.register(Opportunity)
class OpportunityAdmin(admin.ModelAdmin):
    list_display = [
        'opportunity_id', 'name', 'colored_stage', 'amount', 'probability_bar',
        'close_date', 'assigned_to', 'account', 'days_until_close', 'is_active'
    ]
    list_filter = [
        'stage', 'product_type', 'lead_source', 'region',
        'assigned_to', 'is_active', 'close_date', 'created_at'
    ]
    search_fields = ['name', 'opportunity_id', 'account__name', 'contact__first_name', 'contact__last_name']
    readonly_fields = ['opportunity_id', 'expected_revenue', 'created_by', 'created_at', 'updated_at']
    raw_id_fields = ['account', 'contact', 'campaign']
    date_hierarchy = 'close_date'
    inlines = [OpportunityProductInline, QuoteInline, CommissionInline]
    
    fieldsets = (
        ('Información Básica', {
            'fields': ('opportunity_id', 'name', 'description')
        }),
        ('Entidades Relacionadas', {
            'fields': ('account', 'contact', 'campaign')
        }),
        ('Información de Venta', {
            'fields': ('stage', 'probability', 'amount', 'expected_revenue', 'product_type', 'quantity')
        }),
        ('Cronograma', {
            'fields': ('close_date', 'closed_date')
        }),
        ('Origen y Competencia', {
            'fields': ('lead_source', 'competitors'),
            'classes': ('collapse',)
        }),
        ('Asignación', {
            'fields': ('assigned_to', 'sales_team', 'region')
        }),
        ('Estado y Seguimiento', {
            'fields': ('is_active', 'requires_follow_up', 'last_activity_date')
        }),
        ('Seguimiento del Sistema', {
            'fields': ('created_by', 'created_at', 'updated_at'),
            'classes': ('collapse',)
        }),
    )
    
    def save_model(self, request, obj, form, change):
        if not change:
            obj.created_by = request.user
        super().save_model(request, obj, form, change)
    
    actions = ['mark_as_won', 'mark_as_lost', 'advance_stage', 'calculate_expected_revenue']
    
    def colored_stage(self, obj):
        """Display stage with color coding."""
        colors = {
            'qualification': '#ffc107',
            'needs_analysis': '#17a2b8',
            'proposal': '#6f42c1',
            'negotiation': '#fd7e14',
            'closed_won': '#28a745',
            'closed_lost': '#dc3545',
        }
        color = colors.get(obj.stage, '#6c757d')
        return format_html(
            '<span style="color: {}; font-weight: bold;">{}</span>',
            color, obj.get_stage_display()
        )
    colored_stage.short_description = 'Etapa'
    colored_stage.admin_order_field = 'stage'
    
    def probability_bar(self, obj):
        """Display probability as a progress bar."""
        color = 'danger' if obj.probability < 30 else 'warning' if obj.probability < 60 else 'success'
        return format_html(
            '<div class="progress" style="width: 100px;">'
            '<div class="progress-bar bg-{}" style="width: {}%">{:1.0f}%</div>'
            '</div>',
            color, obj.probability, obj.probability
        )
    probability_bar.short_description = 'Probabilidad'
    probability_bar.admin_order_field = 'probability'
    
    def days_until_close(self, obj):
        """Calculate days until close date."""
        from django.utils import timezone
        if obj.close_date:
            delta = obj.close_date - timezone.now().date()
            days = delta.days
            if days < 0:
                return format_html('<span style="color: red;">Vencido ({} días)</span>', abs(days))
            elif days == 0:
                return format_html('<span style="color: orange;">Hoy</span>')
            elif days <= 7:
                return format_html('<span style="color: orange;">{} días</span>', days)
            else:
                return f'{days} días'
        return '-'
    days_until_close.short_description = 'Días para cierre'
    
    def mark_as_won(self, request, queryset):
        updated = queryset.exclude(stage='closed_won').update(stage='closed_won')
        self.message_user(request, f'{updated} oportunidades marcadas como ganadas.')
    mark_as_won.short_description = 'Marcar como ganadas'
    
    def mark_as_lost(self, request, queryset):
        updated = queryset.exclude(stage='closed_lost').update(stage='closed_lost')
        self.message_user(request, f'{updated} oportunidades marcadas como perdidas.')
    mark_as_lost.short_description = 'Marcar como perdidas'
    
    def advance_stage(self, request, queryset):
        stage_progression = {
            'qualification': 'needs_analysis',
            'needs_analysis': 'proposal',
            'proposal': 'negotiation',
            'negotiation': 'closed_won',
        }
        updated = 0
        for obj in queryset:
            if obj.stage in stage_progression:
                obj.stage = stage_progression[obj.stage]
                obj.save()
                updated += 1
        self.message_user(request, f'{updated} oportunidades avanzadas a la siguiente etapa.')
    advance_stage.short_description = 'Avanzar a siguiente etapa'
    
    def calculate_expected_revenue(self, request, queryset):
        for obj in queryset:
            obj.save()  # This will trigger the expected revenue calculation
        self.message_user(request, f'Ingresos esperados recalculados para {queryset.count()} oportunidades.')
    calculate_expected_revenue.short_description = 'Recalcular ingresos esperados'


@admin.register(OpportunityProduct)
class OpportunityProductAdmin(admin.ModelAdmin):
    list_display = ['opportunity', 'product_name', 'quantity', 'unit_price', 'discount', 'total_price']
    list_filter = ['opportunity__stage', 'created_at']
    search_fields = ['product_name', 'product_code', 'opportunity__name']
    readonly_fields = ['total_price']
    raw_id_fields = ['opportunity']


@admin.register(Quote)
class QuoteAdmin(admin.ModelAdmin):
    list_display = [
        'quote_number', 'opportunity', 'subject', 'total',
        'issue_date', 'expiration_date', 'status'
    ]
    list_filter = ['status', 'issue_date', 'expiration_date', 'created_at']
    search_fields = ['quote_number', 'subject', 'opportunity__name']
    readonly_fields = ['quote_number', 'tax_amount', 'total', 'created_by', 'created_at', 'updated_at']
    raw_id_fields = ['opportunity']
    date_hierarchy = 'issue_date'
    
    fieldsets = (
        ('Información Básica', {
            'fields': ('quote_number', 'opportunity', 'subject', 'description')
        }),
        ('Montos', {
            'fields': ('subtotal', 'discount', 'tax_rate', 'tax_amount', 'total')
        }),
        ('Cronograma', {
            'fields': ('issue_date', 'expiration_date')
        }),
        ('Estado', {
            'fields': ('status',)
        }),
        ('Seguimiento', {
            'fields': ('created_by', 'created_at', 'updated_at'),
            'classes': ('collapse',)
        }),
    )
    
    def save_model(self, request, obj, form, change):
        if not change:
            obj.created_by = request.user
        super().save_model(request, obj, form, change)


@admin.register(Commission)
class CommissionAdmin(admin.ModelAdmin):
    list_display = [
        'opportunity', 'sales_rep', 'sale_amount', 'commission_rate',
        'commission_amount', 'is_paid', 'paid_date'
    ]
    list_filter = ['is_paid', 'is_special_campaign', 'paid_date', 'created_at']
    search_fields = ['opportunity__name', 'opportunity__opportunity_id', 'sales_rep__first_name', 'sales_rep__last_name']
    readonly_fields = ['commission_amount', 'created_at', 'updated_at']
    raw_id_fields = ['opportunity', 'sales_rep']
    
    fieldsets = (
        ('Información Básica', {
            'fields': ('opportunity', 'sales_rep')
        }),
        ('Cálculo de Comisión', {
            'fields': ('sale_amount', 'commission_rate', 'commission_amount')
        }),
        ('Condiciones Especiales', {
            'fields': ('is_special_campaign', 'campaign_bonus')
        }),
        ('Estado de Pago', {
            'fields': ('is_paid', 'paid_date')
        }),
        ('Seguimiento', {
            'fields': ('created_at', 'updated_at'),
            'classes': ('collapse',)
        }),
    )
    
    actions = ['mark_as_paid', 'recalculate_commission']
    
    def mark_as_paid(self, request, queryset):
        from django.utils import timezone
        updated = queryset.filter(is_paid=False).update(
            is_paid=True,
            paid_date=timezone.now().date()
        )
        self.message_user(request, f'{updated} comisiones marcadas como pagadas.')
    mark_as_paid.short_description = 'Marcar como pagadas'
    
    def recalculate_commission(self, request, queryset):
        for commission in queryset:
            commission.calculate_commission()
            commission.save()
        self.message_user(request, f'{queryset.count()} comisiones recalculadas.')
    recalculate_commission.short_description = 'Recalcular comisiones'