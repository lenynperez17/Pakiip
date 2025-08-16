#!/usr/bin/env python
"""
Script de inicialización rápida del proyecto
Ejecutar con: python manage.py shell -c "exec(open('init_project.py').read())"
"""

from django.contrib.auth.models import User
from apps.contacts.models import Account, Contact
from decimal import Decimal

def create_basic_data():
    print("🚀 Inicializando proyecto NYNEL CRM...")
    
    # Crear superusuario si no existe
    if not User.objects.filter(username='admin').exists():
        admin = User.objects.create_superuser(
            username='admin',
            email='admin@nynel.com',
            password='admin123',
            first_name='Administrador',
            last_name='Sistema'
        )
        print("✅ Usuario admin creado - usuario: admin, contraseña: admin123")
    else:
        admin = User.objects.get(username='admin')
        print("ℹ️  Usuario admin ya existe")
    
    # Crear usuarios adicionales
    users_data = [
        {
            'username': 'vendedor1',
            'email': 'vendedor1@nynel.com',
            'password': 'vendedor123',
            'first_name': 'Carlos',
            'last_name': 'Mendoza',
            'is_staff': True
        },
        {
            'username': 'marketing1',
            'email': 'marketing1@nynel.com',
            'password': 'marketing123',
            'first_name': 'María',
            'last_name': 'García',
            'is_staff': True
        },
        {
            'username': 'soporte1',
            'email': 'soporte1@nynel.com',
            'password': 'soporte123',
            'first_name': 'Juan',
            'last_name': 'Pérez',
            'is_staff': True
        }
    ]
    
    for user_data in users_data:
        if not User.objects.filter(username=user_data['username']).exists():
            user = User.objects.create_user(**user_data)
            print(f"✅ Usuario {user_data['username']} creado")
    
    # Crear cuentas demo
    accounts_data = [
        {
            'name': 'Tech Solutions SAC',
            'ruc': '20123456789',
            'account_type': 'customer',
            'industry': 'Tecnología',
            'annual_revenue': Decimal('500000.00'),
            'employees': 50,
            'website': 'https://techsolutions.com.pe',
            'billing_city': 'Lima'
        },
        {
            'name': 'Innovate Corp EIRL',
            'ruc': '20987654321',
            'account_type': 'prospect',
            'industry': 'Consultoría',
            'annual_revenue': Decimal('750000.00'),
            'employees': 25,
            'website': 'https://innovatecorp.com.pe',
            'billing_city': 'Arequipa'
        }
    ]
    
    for account_data in accounts_data:
        if not Account.objects.filter(ruc=account_data['ruc']).exists():
            Account.objects.create(
                **account_data,
                billing_country='Perú',
                created_by=admin,
                assigned_to=admin
            )
            print(f"✅ Cuenta {account_data['name']} creada")
    
    # Crear contactos demo
    contacts_data = [
        {
            'first_name': 'Luis',
            'last_name': 'Rodríguez',
            'email': 'luis.rodriguez@techsolutions.com.pe',
            'phone': '+51987654321',
            'job_title': 'Gerente General',
            'dni': '12345678',
            'account_ruc': '20123456789'
        },
        {
            'first_name': 'Ana',
            'last_name': 'Torres',
            'email': 'ana.torres@innovatecorp.com.pe',
            'phone': '+51876543210',
            'job_title': 'Directora de TI',
            'dni': '87654321',
            'account_ruc': '20987654321'
        }
    ]
    
    for contact_data in contacts_data:
        if not Contact.objects.filter(email=contact_data['email']).exists():
            account = Account.objects.get(ruc=contact_data.pop('account_ruc'))
            Contact.objects.create(
                **contact_data,
                account=account,
                created_by=admin,
                assigned_to=admin
            )
            print(f"✅ Contacto {contact_data['first_name']} {contact_data['last_name']} creado")
    
    print("\n🎉 ¡Proyecto inicializado correctamente!")
    print("\n🔑 Credenciales de acceso:")
    print("   👤 Admin: admin / admin123")
    print("   💼 Vendedor: vendedor1 / vendedor123")
    print("   📧 Marketing: marketing1 / marketing123")
    print("   🎧 Soporte: soporte1 / soporte123")
    print("\n🌐 URLs de acceso:")
    print("   🎨 Frontend: http://localhost:3000")
    print("   🔧 Admin Django: http://localhost:8000/admin")
    print("   📚 API Docs: http://localhost:8000/swagger/")
    print("   🔌 API Backend: http://localhost:8000/api/v1/")
    print("\n📊 Datos demo creados:")
    print(f"   • {Account.objects.count()} Cuentas")
    print(f"   • {Contact.objects.count()} Contactos")
    print(f"   • {User.objects.count()} Usuarios")

if __name__ == '__main__':
    create_basic_data()
else:
    create_basic_data()