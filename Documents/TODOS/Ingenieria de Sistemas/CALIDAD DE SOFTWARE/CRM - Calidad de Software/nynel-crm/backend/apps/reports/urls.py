from django.urls import path, include
from rest_framework.routers import DefaultRouter
from . import views

router = DefaultRouter()
router.register(r'reports', views.ReportViewSet, basename='report')
router.register(r'report-executions', views.ReportExecutionViewSet, basename='reportexecution')
router.register(r'dashboards', views.DashboardViewSet, basename='dashboard')
router.register(r'dashboard-widgets', views.DashboardWidgetViewSet, basename='dashboardwidget')
router.register(r'sales-reports', views.SalesReportViewSet, basename='salesreport')
router.register(r'marketing-reports', views.MarketingReportViewSet, basename='marketingreport')
router.register(r'support-reports', views.SupportReportViewSet, basename='supportreport')

app_name = 'reports'

urlpatterns = [
    path('', include(router.urls)),
]