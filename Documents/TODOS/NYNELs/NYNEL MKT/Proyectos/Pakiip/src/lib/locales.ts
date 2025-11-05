// src/lib/locales.ts

// A simple structure for UI strings. In a real-world scenario,
// this would be replaced by a more robust i18n library like `next-intl` or `react-i18next`.
// This centralization is the first step towards scalability.

export const es = {
  // General
  appName: 'MercadoListo',
  view_all_stores: 'Ver Todas las Tiendas',
  explore_by_category: 'Explorar por Categoría',
  all_stores: 'Todas las Tiendas',
  featured_stores: 'Tiendas Destacadas',
  no_stores_found: 'No se encontraron tiendas que coincidan con tu búsqueda.',
  
  // Header & Navigation
  nav_stores: 'Tiendas',
  nav_offers: 'Ofertas',
  nav_recommendations: 'Recomendaciones',
  
  // Product Card
  add_to_cart: 'Añadir al Carrito',
  select_options: 'Seleccionar Opciones',
  out_of_stock: 'Agotado',

  // Checkout Page
  checkout_title: 'Finalizar Compra',
  checkout_details_title: 'Detalles de Compra',
  checkout_contact_shipping: 'Contacto y Envío',
  checkout_payment_method: 'Método de Pago',
  checkout_card_details: 'Detalles de la Tarjeta',
  checkout_order_summary: 'Resumen del Pedido',
  checkout_subtotal: 'Subtotal',
  checkout_taxes: 'Impuestos',
  checkout_shipping: 'Envío',
  checkout_total: 'Total',
  checkout_place_order: 'Realizar Pedido',
  
  // Admin Dashboard
  admin_dashboard_title: 'Resumen',
  admin_dashboard_description: 'Una vista general de tu plataforma.',
  admin_total_stores: 'Tiendas Totales',
  admin_active_drivers: 'Repartidores Activos',
  admin_sales_today: 'Ventas del Día',

};

export type Locale = typeof es;

// For now, we only have Spanish. Adding more languages would involve creating
// similar objects (e.g., `ptBR`) and a mechanism to select the active locale.
export const locales = {
  es,
};
