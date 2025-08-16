from django.db import models
from django.contrib.auth import get_user_model
from django.core.validators import RegexValidator, EmailValidator
from django.utils import timezone

User = get_user_model()


class Account(models.Model):
    """Company or organization account."""
    ACCOUNT_TYPE_CHOICES = [
        ('customer', 'Cliente'),
        ('prospect', 'Prospecto'),
        ('partner', 'Partner'),
        ('vendor', 'Proveedor'),
    ]
    
    name = models.CharField(max_length=255, verbose_name="Nombre de la empresa")
    account_type = models.CharField(max_length=20, choices=ACCOUNT_TYPE_CHOICES, default='prospect')
    industry = models.CharField(max_length=100, blank=True, verbose_name="Industria")
    annual_revenue = models.DecimalField(max_digits=12, decimal_places=2, null=True, blank=True, verbose_name="Ingresos anuales")
    employees = models.IntegerField(null=True, blank=True, verbose_name="Número de empleados")
    website = models.URLField(blank=True, verbose_name="Sitio web")
    
    # Tax identification
    ruc_validator = RegexValidator(regex=r'^\d{11}$', message='RUC debe tener 11 dígitos')
    ruc = models.CharField(max_length=11, unique=True, validators=[ruc_validator], verbose_name="RUC")
    
    # Address
    billing_street = models.TextField(blank=True, verbose_name="Dirección de facturación")
    billing_city = models.CharField(max_length=100, blank=True, verbose_name="Ciudad")
    billing_state = models.CharField(max_length=100, blank=True, verbose_name="Estado/Provincia")
    billing_postal_code = models.CharField(max_length=20, blank=True, verbose_name="Código postal")
    billing_country = models.CharField(max_length=100, default='Perú', verbose_name="País")
    
    # Tracking
    created_by = models.ForeignKey(User, on_delete=models.SET_NULL, null=True, related_name='created_accounts')
    assigned_to = models.ForeignKey(User, on_delete=models.SET_NULL, null=True, related_name='assigned_accounts')
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)
    
    class Meta:
        verbose_name = "Cuenta"
        verbose_name_plural = "Cuentas"
        ordering = ['-created_at']
    
    def __str__(self):
        return self.name


class Contact(models.Model):
    """Individual contact/person."""
    SALUTATION_CHOICES = [
        ('mr', 'Sr.'),
        ('mrs', 'Sra.'),
        ('ms', 'Srta.'),
        ('dr', 'Dr.'),
        ('prof', 'Prof.'),
    ]
    
    # Personal information
    salutation = models.CharField(max_length=10, choices=SALUTATION_CHOICES, blank=True)
    first_name = models.CharField(max_length=100, verbose_name="Nombre")
    last_name = models.CharField(max_length=100, verbose_name="Apellido")
    job_title = models.CharField(max_length=100, blank=True, verbose_name="Cargo")
    department = models.CharField(max_length=100, blank=True, verbose_name="Departamento")
    
    # Contact information
    email = models.EmailField(unique=True, validators=[EmailValidator()], verbose_name="Correo electrónico")
    phone_validator = RegexValidator(
        regex=r'^\+?1?\d{9,15}$',
        message="Número de teléfono debe tener entre 9 y 15 dígitos"
    )
    phone = models.CharField(max_length=20, blank=True, validators=[phone_validator], verbose_name="Teléfono")
    mobile = models.CharField(max_length=20, blank=True, validators=[phone_validator], verbose_name="Móvil")
    
    # DNI validator for Peru
    dni_validator = RegexValidator(regex=r'^\d{8}$', message='DNI debe tener 8 dígitos')
    dni = models.CharField(max_length=8, unique=True, null=True, blank=True, validators=[dni_validator], verbose_name="DNI")
    
    # Relationships
    account = models.ForeignKey(Account, on_delete=models.CASCADE, related_name='contacts', null=True, blank=True)
    reports_to = models.ForeignKey('self', on_delete=models.SET_NULL, null=True, blank=True, related_name='subordinates')
    
    # Address (can be different from account)
    street = models.TextField(blank=True, verbose_name="Dirección")
    city = models.CharField(max_length=100, blank=True, verbose_name="Ciudad")
    state = models.CharField(max_length=100, blank=True, verbose_name="Estado/Provincia")
    postal_code = models.CharField(max_length=20, blank=True, verbose_name="Código postal")
    country = models.CharField(max_length=100, default='Perú', verbose_name="País")
    
    # Preferences
    preferred_contact_method = models.CharField(
        max_length=20,
        choices=[('email', 'Email'), ('phone', 'Teléfono'), ('mobile', 'Móvil')],
        default='email'
    )
    do_not_call = models.BooleanField(default=False, verbose_name="No llamar")
    do_not_email = models.BooleanField(default=False, verbose_name="No enviar email")
    
    # Tracking
    created_by = models.ForeignKey(User, on_delete=models.SET_NULL, null=True, related_name='created_contacts')
    assigned_to = models.ForeignKey(User, on_delete=models.SET_NULL, null=True, related_name='assigned_contacts')
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)
    last_activity_date = models.DateTimeField(null=True, blank=True)
    
    class Meta:
        verbose_name = "Contacto"
        verbose_name_plural = "Contactos"
        ordering = ['-created_at']
        indexes = [
            models.Index(fields=['email']),
            models.Index(fields=['last_name', 'first_name']),
        ]
    
    def __str__(self):
        return f"{self.first_name} {self.last_name}"
    
    @property
    def full_name(self):
        return f"{self.first_name} {self.last_name}"
    
    def save(self, *args, **kwargs):
        if not self.last_activity_date:
            self.last_activity_date = timezone.now()
        super().save(*args, **kwargs)


class Activity(models.Model):
    """Activities/interactions with contacts."""
    ACTIVITY_TYPE_CHOICES = [
        ('call', 'Llamada'),
        ('email', 'Email'),
        ('meeting', 'Reunión'),
        ('task', 'Tarea'),
        ('note', 'Nota'),
    ]
    
    STATUS_CHOICES = [
        ('planned', 'Planificado'),
        ('completed', 'Completado'),
        ('cancelled', 'Cancelado'),
    ]
    
    activity_type = models.CharField(max_length=20, choices=ACTIVITY_TYPE_CHOICES)
    subject = models.CharField(max_length=255, verbose_name="Asunto")
    description = models.TextField(blank=True, verbose_name="Descripción")
    
    # Related entities
    contact = models.ForeignKey(Contact, on_delete=models.CASCADE, related_name='activities', null=True, blank=True)
    account = models.ForeignKey(Account, on_delete=models.CASCADE, related_name='activities', null=True, blank=True)
    
    # Timing
    due_date = models.DateTimeField(verbose_name="Fecha de vencimiento")
    duration = models.IntegerField(null=True, blank=True, verbose_name="Duración (minutos)")
    status = models.CharField(max_length=20, choices=STATUS_CHOICES, default='planned')
    completed_date = models.DateTimeField(null=True, blank=True)
    
    # Assignment
    assigned_to = models.ForeignKey(User, on_delete=models.CASCADE, related_name='activities')
    created_by = models.ForeignKey(User, on_delete=models.SET_NULL, null=True, related_name='created_activities')
    
    # Tracking
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)
    
    class Meta:
        verbose_name = "Actividad"
        verbose_name_plural = "Actividades"
        ordering = ['-due_date']
    
    def __str__(self):
        return f"{self.get_activity_type_display()} - {self.subject}"
    
    def save(self, *args, **kwargs):
        if self.status == 'completed' and not self.completed_date:
            self.completed_date = timezone.now()
        super().save(*args, **kwargs)
        
        # Update last activity date for related contact
        if self.contact and self.status == 'completed':
            self.contact.last_activity_date = self.completed_date or timezone.now()
            self.contact.save(update_fields=['last_activity_date'])