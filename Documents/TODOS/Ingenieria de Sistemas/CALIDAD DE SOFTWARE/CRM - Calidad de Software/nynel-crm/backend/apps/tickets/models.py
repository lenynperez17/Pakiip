from django.db import models
from django.contrib.auth import get_user_model
from django.utils import timezone
import uuid

User = get_user_model()


class Ticket(models.Model):
    """Support tickets/cases."""
    PRIORITY_CHOICES = [
        ('low', 'Baja'),
        ('medium', 'Media'),
        ('high', 'Alta'),
        ('urgent', 'Urgente'),
    ]
    
    STATUS_CHOICES = [
        ('new', 'Nuevo'),
        ('open', 'Abierto'),
        ('pending', 'Pendiente'),
        ('on_hold', 'En Espera'),
        ('resolved', 'Resuelto'),
        ('closed', 'Cerrado'),
        ('cancelled', 'Cancelado'),
    ]
    
    TYPE_CHOICES = [
        ('question', 'Pregunta'),
        ('problem', 'Problema'),
        ('feature_request', 'Solicitud de Función'),
        ('bug', 'Error/Bug'),
        ('complaint', 'Queja'),
        ('other', 'Otro'),
    ]
    
    # Ticket identification
    ticket_number = models.CharField(max_length=20, unique=True, editable=False, verbose_name="Número de ticket")
    
    # Basic information
    subject = models.CharField(max_length=255, verbose_name="Asunto")
    description = models.TextField(verbose_name="Descripción")
    ticket_type = models.CharField(max_length=20, choices=TYPE_CHOICES, default='question')
    
    # Related entities
    contact = models.ForeignKey('contacts.Contact', on_delete=models.CASCADE, related_name='tickets')
    account = models.ForeignKey('contacts.Account', on_delete=models.CASCADE, null=True, blank=True, related_name='tickets')
    
    # Priority and status
    priority = models.CharField(max_length=10, choices=PRIORITY_CHOICES, default='medium')
    status = models.CharField(max_length=20, choices=STATUS_CHOICES, default='new')
    
    # Assignment
    assigned_to = models.ForeignKey(User, on_delete=models.SET_NULL, null=True, blank=True, related_name='assigned_tickets')
    assigned_team = models.CharField(max_length=100, blank=True, verbose_name="Equipo asignado")
    
    # SLA tracking
    sla_due_date = models.DateTimeField(null=True, blank=True, verbose_name="Fecha límite SLA")
    first_response_date = models.DateTimeField(null=True, blank=True, verbose_name="Primera respuesta")
    resolution_date = models.DateTimeField(null=True, blank=True, verbose_name="Fecha de resolución")
    closed_date = models.DateTimeField(null=True, blank=True, verbose_name="Fecha de cierre")
    
    # Customer satisfaction
    satisfaction_rating = models.IntegerField(
        null=True, blank=True,
        choices=[(i, i) for i in range(1, 6)],  # 1-5 stars
        verbose_name="Calificación de satisfacción"
    )
    satisfaction_comment = models.TextField(blank=True, verbose_name="Comentario de satisfacción")
    
    # Tracking
    created_by = models.ForeignKey(User, on_delete=models.SET_NULL, null=True, related_name='created_tickets')
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)
    
    # Internal notes
    internal_notes = models.TextField(blank=True, verbose_name="Notas internas")
    
    class Meta:
        verbose_name = "Ticket"
        verbose_name_plural = "Tickets"
        ordering = ['-created_at']
        indexes = [
            models.Index(fields=['status', 'priority']),
            models.Index(fields=['assigned_to', 'status']),
        ]
    
    def __str__(self):
        return f"{self.ticket_number} - {self.subject}"
    
    def save(self, *args, **kwargs):
        # Auto-generate ticket number
        if not self.ticket_number:
            year = timezone.now().year
            month = timezone.now().month
            last_ticket = Ticket.objects.filter(
                ticket_number__startswith=f'T{year}{month:02d}'
            ).order_by('ticket_number').last()
            
            if last_ticket:
                last_number = int(last_ticket.ticket_number[7:])
                self.ticket_number = f'T{year}{month:02d}{str(last_number + 1).zfill(4)}'
            else:
                self.ticket_number = f'T{year}{month:02d}0001'
        
        # Calculate SLA due date based on priority
        if not self.sla_due_date and self.created_at:
            if self.priority == 'urgent':
                self.sla_due_date = self.created_at + timezone.timedelta(hours=4)
            elif self.priority == 'high':
                self.sla_due_date = self.created_at + timezone.timedelta(hours=8)
            elif self.priority == 'medium':
                self.sla_due_date = self.created_at + timezone.timedelta(days=1)
            else:  # low
                self.sla_due_date = self.created_at + timezone.timedelta(days=3)
        
        # Update dates based on status changes
        if self.status == 'resolved' and not self.resolution_date:
            self.resolution_date = timezone.now()
        elif self.status == 'closed' and not self.closed_date:
            self.closed_date = timezone.now()
        
        super().save(*args, **kwargs)
    
    @property
    def is_overdue(self):
        """Check if ticket is overdue based on SLA."""
        if self.sla_due_date and self.status not in ['resolved', 'closed', 'cancelled']:
            return timezone.now() > self.sla_due_date
        return False
    
    @property
    def response_time(self):
        """Calculate first response time in hours."""
        if self.first_response_date and self.created_at:
            delta = self.first_response_date - self.created_at
            return delta.total_seconds() / 3600
        return None
    
    @property
    def resolution_time(self):
        """Calculate resolution time in hours."""
        if self.resolution_date and self.created_at:
            delta = self.resolution_date - self.created_at
            return delta.total_seconds() / 3600
        return None


class TicketComment(models.Model):
    """Comments/replies on tickets."""
    ticket = models.ForeignKey(Ticket, on_delete=models.CASCADE, related_name='comments')
    
    # Comment content
    comment = models.TextField(verbose_name="Comentario")
    is_internal = models.BooleanField(default=False, verbose_name="Nota interna")
    
    # Author
    author = models.ForeignKey(User, on_delete=models.SET_NULL, null=True)
    author_name = models.CharField(max_length=100, blank=True, verbose_name="Nombre del autor")
    author_email = models.EmailField(blank=True, verbose_name="Email del autor")
    
    # Attachments (simplified - in production would use file field)
    has_attachments = models.BooleanField(default=False)
    
    # Tracking
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)
    
    class Meta:
        verbose_name = "Comentario de Ticket"
        verbose_name_plural = "Comentarios de Tickets"
        ordering = ['created_at']
    
    def __str__(self):
        return f"Comentario en {self.ticket.ticket_number} por {self.author_name or self.author}"
    
    def save(self, *args, **kwargs):
        # Update ticket's first response date if this is the first agent response
        if not self.is_internal and self.author and not self.ticket.first_response_date:
            if self.author != self.ticket.created_by:
                self.ticket.first_response_date = timezone.now()
                self.ticket.save(update_fields=['first_response_date'])
        
        # Auto-fill author name if not provided
        if self.author and not self.author_name:
            self.author_name = self.author.get_full_name() or self.author.username
        
        super().save(*args, **kwargs)


class KnowledgeBase(models.Model):
    """Knowledge base articles for self-service."""
    CATEGORY_CHOICES = [
        ('getting_started', 'Comenzando'),
        ('features', 'Características'),
        ('troubleshooting', 'Solución de Problemas'),
        ('faq', 'Preguntas Frecuentes'),
        ('best_practices', 'Mejores Prácticas'),
        ('other', 'Otro'),
    ]
    
    # Article information
    title = models.CharField(max_length=255, verbose_name="Título")
    slug = models.SlugField(unique=True, max_length=255)
    content = models.TextField(verbose_name="Contenido")
    summary = models.TextField(max_length=500, verbose_name="Resumen")
    
    # Categorization
    category = models.CharField(max_length=30, choices=CATEGORY_CHOICES)
    tags = models.CharField(max_length=255, blank=True, verbose_name="Etiquetas")
    
    # Status
    is_published = models.BooleanField(default=False, verbose_name="Publicado")
    is_featured = models.BooleanField(default=False, verbose_name="Destacado")
    
    # Metrics
    views = models.IntegerField(default=0, verbose_name="Vistas")
    helpful_votes = models.IntegerField(default=0, verbose_name="Votos útiles")
    not_helpful_votes = models.IntegerField(default=0, verbose_name="Votos no útiles")
    
    # Related
    related_articles = models.ManyToManyField('self', blank=True, symmetrical=True)
    
    # Tracking
    author = models.ForeignKey(User, on_delete=models.SET_NULL, null=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)
    published_date = models.DateTimeField(null=True, blank=True)
    
    class Meta:
        verbose_name = "Artículo de Base de Conocimientos"
        verbose_name_plural = "Artículos de Base de Conocimientos"
        ordering = ['-created_at']
    
    def __str__(self):
        return self.title
    
    def save(self, *args, **kwargs):
        if self.is_published and not self.published_date:
            self.published_date = timezone.now()
        super().save(*args, **kwargs)
    
    @property
    def helpfulness_score(self):
        """Calculate helpfulness percentage."""
        total_votes = self.helpful_votes + self.not_helpful_votes
        if total_votes > 0:
            return (self.helpful_votes / total_votes) * 100
        return 0
    
    def increment_views(self):
        """Increment view count."""
        self.views += 1
        self.save(update_fields=['views'])


class TicketTemplate(models.Model):
    """Templates for common ticket responses."""
    name = models.CharField(max_length=255, verbose_name="Nombre de la plantilla")
    category = models.CharField(max_length=100, blank=True, verbose_name="Categoría")
    
    # Template content
    subject = models.CharField(max_length=255, blank=True, verbose_name="Asunto")
    content = models.TextField(verbose_name="Contenido")
    
    # Usage
    is_active = models.BooleanField(default=True)
    times_used = models.IntegerField(default=0)
    
    # Available variables
    available_variables = models.TextField(
        blank=True,
        default="{{contact_name}}, {{ticket_number}}, {{agent_name}}",
        verbose_name="Variables disponibles"
    )
    
    # Tracking
    created_by = models.ForeignKey(User, on_delete=models.SET_NULL, null=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)
    
    class Meta:
        verbose_name = "Plantilla de Ticket"
        verbose_name_plural = "Plantillas de Tickets"
        ordering = ['category', 'name']
    
    def __str__(self):
        return f"{self.category} - {self.name}" if self.category else self.name