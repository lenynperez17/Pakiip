from django.urls import path
from .views import (
    LoginView, LogoutView, RegisterView, UserProfileView,
    ChangePasswordView, current_user
)

app_name = 'authentication'

urlpatterns = [
    path('login/', LoginView.as_view(), name='login'),
    path('logout/', LogoutView.as_view(), name='logout'),
    path('register/', RegisterView.as_view(), name='register'),
    path('profile/', UserProfileView.as_view(), name='profile'),
    path('change-password/', ChangePasswordView.as_view(), name='change_password'),
    path('current-user/', current_user, name='current_user'),
]