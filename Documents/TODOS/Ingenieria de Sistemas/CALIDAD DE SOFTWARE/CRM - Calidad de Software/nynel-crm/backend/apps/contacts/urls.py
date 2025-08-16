from django.urls import path, include
from rest_framework.routers import DefaultRouter
from .views import AccountViewSet, ContactViewSet, ActivityViewSet

router = DefaultRouter()
router.register(r'accounts', AccountViewSet)
router.register(r'contacts', ContactViewSet)
router.register(r'activities', ActivityViewSet)

urlpatterns = [
    path('', include(router.urls)),
]