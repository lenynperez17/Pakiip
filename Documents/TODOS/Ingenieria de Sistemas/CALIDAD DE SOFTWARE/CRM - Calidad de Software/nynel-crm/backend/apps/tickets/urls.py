from django.urls import path, include
from rest_framework.routers import DefaultRouter
from . import views

router = DefaultRouter()
router.register(r'tickets', views.TicketViewSet, basename='ticket')
router.register(r'ticket-comments', views.TicketCommentViewSet, basename='ticketcomment')
router.register(r'knowledge-base', views.KnowledgeBaseViewSet, basename='knowledgebase')
router.register(r'ticket-templates', views.TicketTemplateViewSet, basename='tickettemplate')

app_name = 'tickets'

urlpatterns = [
    path('', include(router.urls)),
]