from django.db import models
from django.contrib.auth import get_user_model
from django.core.validators import MinValueValidator, MaxValueValidator, EmailValidator
from django.utils import timezone
import uuid

User = get_user_model()


class Campaign(models.Model):
    """Marketing campaigns."""
    CAMPAIGN_TYPE_CHOICES = [
        ('email', 'Email'),
        ('social_media', 'Redes Sociales'),
        ('webinar', 'Webinar'),
        ('event', 'Evento'),
        ('content', 'Contenido'),
        ('other', 'Otro'),
    ]
    
    STATUS_CHOICES = [
        ('planning', 'Planificación'),
        ('active', 'Activo'),
        ('paused', 'Pausado'),
        ('completed', 'Completado'),
        ('cancelled', 'Cancelado'),
    ]
    
    # Basic information
    name = models.CharField(max_length=255, verbose_name="Nombre de la campaña")
    campaign_type = models.CharField(max_length=30, choices=CAMPAIGN_TYPE_CHOICES)
    description = models.TextField(blank=True, verbose_name="Descripción")
    objective = models.TextField(blank=True, verbose_name="Objetivo")
    
    # Timeline
    start_date = models.DateField(verbose_name="Fecha de inicio")
    end_date = models.DateField(verbose_name="Fecha de fin")
    
    # Budget
    budget = models.DecimalField(max_digits=10, decimal_places=2, default=0, verbose_name="Presupuesto")
    actual_cost = models.DecimalField(max_digits=10, decimal_places=2, default=0, verbose_name="Costo real")
    
    # Status
    status = models.CharField(max_length=20, choices=STATUS_CHOICES, default='planning')
    
    # Target
    target_audience = models.TextField(blank=True, verbose_name="Audiencia objetivo")
    expected_leads = models.IntegerField(default=0, verbose_name="Leads esperados")
    actual_leads = models.IntegerField(default=0, verbose_name="Leads reales")
    
    # Tracking
    created_by = models.ForeignKey(User, on_delete=models.SET_NULL, null=True, related_name='created_campaigns')
    assigned_to = models.ForeignKey(User, on_delete=models.SET_NULL, null=True, related_name='assigned_campaigns')
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)
    
    class Meta:
        verbose_name = "Campaña"
        verbose_name_plural = "Campañas"
        ordering = ['-start_date']
    
    def __str__(self):
        return f"{self.name} ({self.get_campaign_type_display()})"
    
    @property
    def roi(self):
        """Return on Investment calculation."""
        if self.actual_cost > 0:
            # Assuming average opportunity value
            revenue = self.actual_leads * 1000  # This should be calculated from actual opportunities
            return ((revenue - self.actual_cost) / self.actual_cost) * 100
        return 0


class EmailTemplate(models.Model):
    """Email templates for marketing campaigns."""
    name = models.CharField(max_length=255, verbose_name="Nombre de la plantilla")
    subject = models.CharField(max_length=255, verbose_name="Asunto")
    body_html = models.TextField(verbose_name="Contenido HTML")
    body_text = models.TextField(blank=True, verbose_name="Contenido de texto")
    
    # Template variables
    available_variables = models.TextField(
        blank=True,
        default="{{first_name}}, {{last_name}}, {{company}}, {{unsubscribe_link}}",
        verbose_name="Variables disponibles"
    )
    
    # Usage
    is_active = models.BooleanField(default=True)
    times_used = models.IntegerField(default=0)
    
    # Tracking
    created_by = models.ForeignKey(User, on_delete=models.SET_NULL, null=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)
    
    class Meta:
        verbose_name = "Plantilla de Email"
        verbose_name_plural = "Plantillas de Email"
        ordering = ['-created_at']
    
    def __str__(self):
        return self.name


class MailingList(models.Model):
    """Segmented mailing lists."""
    name = models.CharField(max_length=255, verbose_name="Nombre de la lista")
    description = models.TextField(blank=True, verbose_name="Descripción")
    
    # Segmentation criteria
    criteria = models.JSONField(default=dict, blank=True, verbose_name="Criterios de segmentación")
    is_dynamic = models.BooleanField(default=False, verbose_name="Lista dinámica")
    
    # Members
    contacts = models.ManyToManyField('contacts.Contact', through='MailingListMember', related_name='mailing_lists')
    
    # Stats
    member_count = models.IntegerField(default=0, editable=False)
    
    # Tracking
    created_by = models.ForeignKey(User, on_delete=models.SET_NULL, null=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)
    
    class Meta:
        verbose_name = "Lista de Correo"
        verbose_name_plural = "Listas de Correo"
        ordering = ['-created_at']
    
    def __str__(self):
        return f"{self.name} ({self.member_count} miembros)"
    
    def update_member_count(self):
        self.member_count = self.members.filter(is_active=True, is_unsubscribed=False).count()
        self.save(update_fields=['member_count'])


class MailingListMember(models.Model):
    """Members of mailing lists."""
    mailing_list = models.ForeignKey(MailingList, on_delete=models.CASCADE, related_name='members')
    contact = models.ForeignKey('contacts.Contact', on_delete=models.CASCADE)
    
    # Status
    is_active = models.BooleanField(default=True)
    is_unsubscribed = models.BooleanField(default=False)
    unsubscribed_date = models.DateTimeField(null=True, blank=True)
    unsubscribe_reason = models.TextField(blank=True)
    
    # Import tracking
    import_source = models.CharField(max_length=100, blank=True, verbose_name="Fuente de importación")
    
    # Tracking
    added_date = models.DateTimeField(auto_now_add=True)
    added_by = models.ForeignKey(User, on_delete=models.SET_NULL, null=True)
    
    class Meta:
        verbose_name = "Miembro de Lista"
        verbose_name_plural = "Miembros de Lista"
        unique_together = ['mailing_list', 'contact']
    
    def __str__(self):
        return f"{self.contact} - {self.mailing_list}"
    
    def unsubscribe(self, reason=''):
        self.is_unsubscribed = True
        self.unsubscribed_date = timezone.now()
        self.unsubscribe_reason = reason
        self.save()


class EmailCampaign(models.Model):
    """Email marketing campaigns."""
    campaign = models.ForeignKey(Campaign, on_delete=models.CASCADE, related_name='email_campaigns')
    email_template = models.ForeignKey(EmailTemplate, on_delete=models.PROTECT)
    mailing_lists = models.ManyToManyField(MailingList)
    
    # Scheduling
    scheduled_date = models.DateTimeField(verbose_name="Fecha programada")
    sent_date = models.DateTimeField(null=True, blank=True, verbose_name="Fecha de envío")
    
    # Stats
    recipients_count = models.IntegerField(default=0, editable=False)
    sent_count = models.IntegerField(default=0, editable=False)
    opened_count = models.IntegerField(default=0, editable=False)
    clicked_count = models.IntegerField(default=0, editable=False)
    bounced_count = models.IntegerField(default=0, editable=False)
    unsubscribed_count = models.IntegerField(default=0, editable=False)
    
    # Status
    is_sent = models.BooleanField(default=False)
    is_test = models.BooleanField(default=False)
    
    # Tracking
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)
    
    class Meta:
        verbose_name = "Campaña de Email"
        verbose_name_plural = "Campañas de Email"
        ordering = ['-scheduled_date']
    
    def __str__(self):
        return f"Email - {self.campaign.name}"
    
    @property
    def open_rate(self):
        if self.sent_count > 0:
            return (self.opened_count / self.sent_count) * 100
        return 0
    
    @property
    def click_rate(self):
        if self.sent_count > 0:
            return (self.clicked_count / self.sent_count) * 100
        return 0


class EmailTracking(models.Model):
    """Track email interactions."""
    ACTION_CHOICES = [
        ('sent', 'Enviado'),
        ('opened', 'Abierto'),
        ('clicked', 'Click'),
        ('bounced', 'Rebotado'),
        ('unsubscribed', 'Desuscrito'),
        ('marked_spam', 'Marcado como Spam'),
    ]
    
    email_campaign = models.ForeignKey(EmailCampaign, on_delete=models.CASCADE, related_name='tracking_events')
    contact = models.ForeignKey('contacts.Contact', on_delete=models.CASCADE)
    
    # Tracking
    tracking_id = models.UUIDField(default=uuid.uuid4, editable=False, unique=True)
    action = models.CharField(max_length=20, choices=ACTION_CHOICES)
    timestamp = models.DateTimeField(default=timezone.now)
    
    # Additional info
    ip_address = models.GenericIPAddressField(null=True, blank=True)
    user_agent = models.TextField(blank=True)
    click_url = models.URLField(blank=True)
    
    class Meta:
        verbose_name = "Seguimiento de Email"
        verbose_name_plural = "Seguimientos de Email"
        ordering = ['-timestamp']
        indexes = [
            models.Index(fields=['email_campaign', 'action']),
            models.Index(fields=['contact', 'timestamp']),
        ]
    
    def __str__(self):
        return f"{self.contact} - {self.get_action_display()} - {self.timestamp}"


class Lead(models.Model):
    """Marketing leads before conversion to contacts."""
    SOURCE_CHOICES = [
        ('website_form', 'Formulario Web'),
        ('landing_page', 'Landing Page'),
        ('social_media', 'Redes Sociales'),
        ('webinar', 'Webinar'),
        ('trade_show', 'Feria Comercial'),
        ('referral', 'Referencia'),
        ('import', 'Importación'),
        ('other', 'Otro'),
    ]
    
    STATUS_CHOICES = [
        ('new', 'Nuevo'),
        ('contacted', 'Contactado'),
        ('qualified', 'Calificado'),
        ('unqualified', 'No Calificado'),
        ('converted', 'Convertido'),
    ]
    
    # Basic information
    first_name = models.CharField(max_length=100, verbose_name="Nombre")
    last_name = models.CharField(max_length=100, verbose_name="Apellido")
    email = models.EmailField(validators=[EmailValidator()], verbose_name="Email")
    phone = models.CharField(max_length=20, blank=True, verbose_name="Teléfono")
    company = models.CharField(max_length=255, blank=True, verbose_name="Empresa")
    job_title = models.CharField(max_length=100, blank=True, verbose_name="Cargo")
    
    # Source
    source = models.CharField(max_length=30, choices=SOURCE_CHOICES)
    campaign = models.ForeignKey(Campaign, on_delete=models.SET_NULL, null=True, blank=True, related_name='leads')
    
    # Lead scoring
    score = models.IntegerField(
        default=0,
        validators=[MinValueValidator(0), MaxValueValidator(100)],
        verbose_name="Puntuación"
    )
    
    # Status
    status = models.CharField(max_length=20, choices=STATUS_CHOICES, default='new')
    
    # Assignment
    assigned_to = models.ForeignKey(User, on_delete=models.SET_NULL, null=True, blank=True, related_name='leads')
    
    # Conversion
    is_converted = models.BooleanField(default=False)
    converted_contact = models.ForeignKey('contacts.Contact', on_delete=models.SET_NULL, null=True, blank=True)
    converted_date = models.DateTimeField(null=True, blank=True)
    
    # Tracking
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)
    
    class Meta:
        verbose_name = "Lead"
        verbose_name_plural = "Leads"
        ordering = ['-created_at']
    
    def __str__(self):
        return f"{self.first_name} {self.last_name} - {self.company or 'Sin empresa'}"
    
    def calculate_score(self):
        """Calculate lead score based on various factors."""
        score = 0
        
        # Email domain scoring
        if self.email:
            domain = self.email.split('@')[1].lower()
            if domain.endswith('.edu'):
                score += 5
            elif any(provider in domain for provider in ['gmail', 'hotmail', 'yahoo']):
                score -= 5
            else:
                score += 10  # Corporate email
        
        # Company information
        if self.company:
            score += 10
        
        # Job title scoring
        if self.job_title:
            score += 5
            executive_titles = ['CEO', 'CTO', 'CFO', 'Director', 'Gerente', 'Manager']
            if any(title.lower() in self.job_title.lower() for title in executive_titles):
                score += 15
        
        # Phone provided
        if self.phone:
            score += 10
        
        # Campaign source
        if self.source in ['webinar', 'trade_show']:
            score += 20
        elif self.source == 'referral':
            score += 25
        
        # Engagement (would need additional tracking)
        # This is simplified for the example
        
        self.score = min(max(score, 0), 100)  # Keep between 0-100
        return self.score
    
    def save(self, *args, **kwargs):
        if not self.score:
            self.calculate_score()
        super().save(*args, **kwargs)