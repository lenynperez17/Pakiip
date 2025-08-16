from django.db import models
from django.contrib.auth import get_user_model
from django.core.validators import MinValueValidator, MaxValueValidator
from django.utils import timezone
from decimal import Decimal

User = get_user_model()


class Opportunity(models.Model):
    """Sales opportunity/deal."""
    STAGE_CHOICES = [
        ('qualification', 'Calificación'),
        ('needs_analysis', 'Análisis de Necesidades'),
        ('proposal', 'Propuesta Presentada'),
        ('negotiation', 'Negociación'),
        ('closed_won', 'Cerrado Ganado'),
        ('closed_lost', 'Cerrado Perdido'),
    ]
    
    LEAD_SOURCE_CHOICES = [
        ('website', 'Sitio Web'),
        ('referral', 'Referencia'),
        ('campaign', 'Campaña'),
        ('trade_show', 'Feria Comercial'),
        ('cold_call', 'Llamada en Frío'),
        ('partner', 'Partner'),
        ('other', 'Otro'),
    ]
    
    PRODUCT_TYPE_CHOICES = [
        ('software_premium', 'Software Premium'),
        ('software_basic', 'Software Básico'),
        ('service_consultancy', 'Servicio Consultoría'),
        ('hardware_basic', 'Hardware Básico'),
        ('other', 'Otro'),
    ]
    
    # Basic information
    name = models.CharField(max_length=255, verbose_name="Nombre de la oportunidad")
    opportunity_id = models.CharField(max_length=20, unique=True, verbose_name="ID de oportunidad")
    description = models.TextField(blank=True, verbose_name="Descripción")
    
    # Related entities
    account = models.ForeignKey('contacts.Account', on_delete=models.CASCADE, related_name='opportunities')
    contact = models.ForeignKey('contacts.Contact', on_delete=models.SET_NULL, null=True, blank=True, related_name='opportunities')
    
    # Sales information
    stage = models.CharField(max_length=30, choices=STAGE_CHOICES, default='qualification')
    probability = models.IntegerField(
        validators=[MinValueValidator(0), MaxValueValidator(100)],
        default=10,
        verbose_name="Probabilidad (%)"
    )
    amount = models.DecimalField(max_digits=12, decimal_places=2, verbose_name="Valor")
    expected_revenue = models.DecimalField(max_digits=12, decimal_places=2, editable=False, verbose_name="Ingreso esperado")
    
    # Product information
    product_type = models.CharField(max_length=30, choices=PRODUCT_TYPE_CHOICES, default='other')
    quantity = models.IntegerField(default=1, validators=[MinValueValidator(1)])
    
    # Timeline
    close_date = models.DateField(verbose_name="Fecha de cierre esperada")
    closed_date = models.DateField(null=True, blank=True, verbose_name="Fecha de cierre real")
    
    # Source
    lead_source = models.CharField(max_length=30, choices=LEAD_SOURCE_CHOICES, blank=True)
    campaign = models.ForeignKey('marketing.Campaign', on_delete=models.SET_NULL, null=True, blank=True, related_name='opportunities')
    
    # Competition
    competitors = models.TextField(blank=True, verbose_name="Competidores")
    
    # Assignment
    assigned_to = models.ForeignKey(User, on_delete=models.CASCADE, related_name='opportunities')
    sales_team = models.CharField(max_length=100, blank=True, verbose_name="Equipo de ventas")
    region = models.CharField(
        max_length=20,
        choices=[('norte', 'Norte'), ('sur', 'Sur'), ('este', 'Este'), ('oeste', 'Oeste'), ('centro', 'Centro')],
        blank=True
    )
    
    # Tracking
    created_by = models.ForeignKey(User, on_delete=models.SET_NULL, null=True, related_name='created_opportunities')
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)
    last_activity_date = models.DateTimeField(null=True, blank=True)
    
    # Flags
    is_active = models.BooleanField(default=True)
    requires_follow_up = models.BooleanField(default=False)
    
    class Meta:
        verbose_name = "Oportunidad"
        verbose_name_plural = "Oportunidades"
        ordering = ['-created_at']
        indexes = [
            models.Index(fields=['stage', 'close_date']),
            models.Index(fields=['assigned_to', 'is_active']),
        ]
    
    def __str__(self):
        return f"{self.opportunity_id} - {self.name}"
    
    def save(self, *args, **kwargs):
        # Auto-generate opportunity ID if not provided
        if not self.opportunity_id:
            year = timezone.now().year
            last_opp = Opportunity.objects.filter(
                opportunity_id__startswith=f'OPT-{year}-'
            ).order_by('opportunity_id').last()
            
            if last_opp:
                last_number = int(last_opp.opportunity_id.split('-')[-1])
                self.opportunity_id = f'OPT-{year}-{str(last_number + 1).zfill(3)}'
            else:
                self.opportunity_id = f'OPT-{year}-001'
        
        # Calculate expected revenue
        self.expected_revenue = (self.amount * Decimal(self.probability)) / Decimal(100)
        
        # Update stage probability mapping
        if self.stage == 'qualification':
            self.probability = min(self.probability, 20)
        elif self.stage == 'needs_analysis':
            self.probability = min(max(self.probability, 20), 40)
        elif self.stage == 'proposal':
            self.probability = min(max(self.probability, 40), 60)
        elif self.stage == 'negotiation':
            self.probability = min(max(self.probability, 60), 80)
        elif self.stage == 'closed_won':
            self.probability = 100
            if not self.closed_date:
                self.closed_date = timezone.now().date()
        elif self.stage == 'closed_lost':
            self.probability = 0
            if not self.closed_date:
                self.closed_date = timezone.now().date()
        
        super().save(*args, **kwargs)


class OpportunityProduct(models.Model):
    """Products/services associated with an opportunity."""
    opportunity = models.ForeignKey(Opportunity, on_delete=models.CASCADE, related_name='products')
    product_name = models.CharField(max_length=255, verbose_name="Nombre del producto")
    product_code = models.CharField(max_length=50, blank=True, verbose_name="Código del producto")
    quantity = models.IntegerField(default=1, validators=[MinValueValidator(1)])
    unit_price = models.DecimalField(max_digits=10, decimal_places=2, verbose_name="Precio unitario")
    discount = models.DecimalField(
        max_digits=5, decimal_places=2, default=0,
        validators=[MinValueValidator(0), MaxValueValidator(100)],
        verbose_name="Descuento (%)"
    )
    total_price = models.DecimalField(max_digits=12, decimal_places=2, editable=False, verbose_name="Precio total")
    
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)
    
    class Meta:
        verbose_name = "Producto de Oportunidad"
        verbose_name_plural = "Productos de Oportunidad"
    
    def __str__(self):
        return f"{self.product_name} - {self.opportunity.name}"
    
    def save(self, *args, **kwargs):
        # Calculate total price
        subtotal = self.quantity * self.unit_price
        discount_amount = subtotal * (self.discount / Decimal(100))
        self.total_price = subtotal - discount_amount
        super().save(*args, **kwargs)


class Quote(models.Model):
    """Sales quotes/proposals."""
    STATUS_CHOICES = [
        ('draft', 'Borrador'),
        ('sent', 'Enviado'),
        ('accepted', 'Aceptado'),
        ('rejected', 'Rechazado'),
        ('expired', 'Expirado'),
    ]
    
    opportunity = models.ForeignKey(Opportunity, on_delete=models.CASCADE, related_name='quotes')
    quote_number = models.CharField(max_length=20, unique=True, verbose_name="Número de cotización")
    
    # Quote details
    subject = models.CharField(max_length=255, verbose_name="Asunto")
    description = models.TextField(blank=True, verbose_name="Descripción")
    
    # Amounts
    subtotal = models.DecimalField(max_digits=12, decimal_places=2, verbose_name="Subtotal")
    discount = models.DecimalField(max_digits=10, decimal_places=2, default=0, verbose_name="Descuento")
    tax_rate = models.DecimalField(max_digits=5, decimal_places=2, default=18, verbose_name="IGV (%)")
    tax_amount = models.DecimalField(max_digits=10, decimal_places=2, editable=False, verbose_name="IGV")
    total = models.DecimalField(max_digits=12, decimal_places=2, editable=False, verbose_name="Total")
    
    # Timeline
    issue_date = models.DateField(default=timezone.now, verbose_name="Fecha de emisión")
    expiration_date = models.DateField(verbose_name="Fecha de vencimiento")
    
    # Status
    status = models.CharField(max_length=20, choices=STATUS_CHOICES, default='draft')
    
    # Tracking
    created_by = models.ForeignKey(User, on_delete=models.SET_NULL, null=True, related_name='created_quotes')
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)
    
    class Meta:
        verbose_name = "Cotización"
        verbose_name_plural = "Cotizaciones"
        ordering = ['-created_at']
    
    def __str__(self):
        return f"{self.quote_number} - {self.opportunity.name}"
    
    def save(self, *args, **kwargs):
        # Auto-generate quote number if not provided
        if not self.quote_number:
            year = timezone.now().year
            last_quote = Quote.objects.filter(
                quote_number__startswith=f'Q-{year}-'
            ).order_by('quote_number').last()
            
            if last_quote:
                last_number = int(last_quote.quote_number.split('-')[-1])
                self.quote_number = f'Q-{year}-{str(last_number + 1).zfill(4)}'
            else:
                self.quote_number = f'Q-{year}-0001'
        
        # Calculate amounts
        self.tax_amount = (self.subtotal - self.discount) * (self.tax_rate / Decimal(100))
        self.total = self.subtotal - self.discount + self.tax_amount
        
        super().save(*args, **kwargs)


class Commission(models.Model):
    """Sales commissions calculation."""
    opportunity = models.ForeignKey(Opportunity, on_delete=models.CASCADE, related_name='commissions')
    sales_rep = models.ForeignKey(User, on_delete=models.CASCADE, related_name='commissions')
    
    # Commission calculation
    sale_amount = models.DecimalField(max_digits=12, decimal_places=2, verbose_name="Monto de venta")
    commission_rate = models.DecimalField(
        max_digits=5, decimal_places=2,
        validators=[MinValueValidator(0), MaxValueValidator(100)],
        verbose_name="Tasa de comisión (%)"
    )
    commission_amount = models.DecimalField(max_digits=10, decimal_places=2, editable=False, verbose_name="Monto de comisión")
    
    # Special conditions
    is_special_campaign = models.BooleanField(default=False, verbose_name="Campaña especial")
    campaign_bonus = models.DecimalField(max_digits=8, decimal_places=2, default=0, verbose_name="Bono de campaña")
    
    # Status
    is_paid = models.BooleanField(default=False, verbose_name="Pagado")
    paid_date = models.DateField(null=True, blank=True, verbose_name="Fecha de pago")
    
    # Tracking
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)
    
    class Meta:
        verbose_name = "Comisión"
        verbose_name_plural = "Comisiones"
        ordering = ['-created_at']
    
    def __str__(self):
        return f"Comisión {self.sales_rep} - {self.opportunity.opportunity_id}"
    
    def calculate_commission(self):
        """Calculate commission based on business rules."""
        base_rate = Decimal('0.05')  # 5% base
        
        # Adjust by product type
        if self.opportunity.product_type == 'software_premium':
            base_rate += Decimal('0.02')
        elif self.opportunity.product_type == 'service_consultancy':
            base_rate += Decimal('0.03')
        
        # Adjust by customer tenure
        if self.opportunity.account:
            account_age_months = (timezone.now().date() - self.opportunity.account.created_at.date()).days / 30
            if account_age_months > 24:
                base_rate += Decimal('0.01')
            elif account_age_months > 12:
                base_rate += Decimal('0.005')
        
        # Adjust by region
        if self.opportunity.region == 'norte':
            if self.sale_amount > 10000:
                base_rate += Decimal('0.005')
            else:
                base_rate += Decimal('0.002')
        elif self.opportunity.region == 'sur':
            if self.sale_amount > 15000:
                base_rate += Decimal('0.007')
        
        # Special campaign bonus
        if self.is_special_campaign and self.opportunity.product_type != 'hardware_basic':
            base_rate = min(base_rate + Decimal('0.015'), Decimal('0.15'))  # Cap at 15%
        
        self.commission_rate = base_rate * 100  # Convert to percentage
        self.commission_amount = self.sale_amount * base_rate + self.campaign_bonus
        
        return self.commission_amount
    
    def save(self, *args, **kwargs):
        if not self.commission_amount:
            self.calculate_commission()
        super().save(*args, **kwargs)