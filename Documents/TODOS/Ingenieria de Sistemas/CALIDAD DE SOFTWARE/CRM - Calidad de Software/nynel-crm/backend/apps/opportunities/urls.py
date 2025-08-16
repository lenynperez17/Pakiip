from django.urls import path, include
from rest_framework.routers import DefaultRouter
from . import views

router = DefaultRouter()
router.register(r'opportunities', views.OpportunityViewSet, basename='opportunity')
router.register(r'opportunity-products', views.OpportunityProductViewSet, basename='opportunityproduct')
router.register(r'quotes', views.QuoteViewSet, basename='quote')
router.register(r'commissions', views.CommissionViewSet, basename='commission')

app_name = 'opportunities'

urlpatterns = [
    path('', include(router.urls)),
]