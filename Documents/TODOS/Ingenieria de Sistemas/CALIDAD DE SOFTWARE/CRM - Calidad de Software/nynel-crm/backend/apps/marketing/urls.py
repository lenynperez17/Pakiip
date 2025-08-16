from django.urls import path, include
from rest_framework.routers import DefaultRouter
from . import views

router = DefaultRouter()
router.register(r'campaigns', views.CampaignViewSet, basename='campaign')
router.register(r'email-templates', views.EmailTemplateViewSet, basename='emailtemplate')
router.register(r'mailing-lists', views.MailingListViewSet, basename='mailinglist')
router.register(r'mailing-list-members', views.MailingListMemberViewSet, basename='mailinglistmember')
router.register(r'email-campaigns', views.EmailCampaignViewSet, basename='emailcampaign')
router.register(r'email-tracking', views.EmailTrackingViewSet, basename='emailtracking')
router.register(r'leads', views.LeadViewSet, basename='lead')

app_name = 'marketing'

urlpatterns = [
    path('', include(router.urls)),
]