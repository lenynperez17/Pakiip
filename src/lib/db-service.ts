/**
 * Servicio de Base de Datos PostgreSQL
 * Reemplaza firestore-service.ts
 */

import { prisma } from "./prisma";
import type {
  User,
  Vendor,
  Product,
  Order,
  OrderItem,
  Driver,
  Category,
  City,
  CitySector,
  DeliveryZone,
  Favor,
  Message,
  Admin,
  PromotionalBanner,
  AnnouncementBanner,
  BankAccount,
  QrPayment,
  AppSettings,
  Cart,
  Reward,
  UserRole,
  EntityStatus,
  OrderStatus,
  VehicleType,
} from "@/generated/prisma";
// Usar number en lugar de Decimal para compatibilidad
type Decimal = number | { toNumber(): number };

// ============================================
// TIPOS DE DATOS COMPATIBLES CON EL FRONTEND
// ============================================

// Convertir Decimal a number para el frontend
function decimalToNumber(val: Decimal | null | undefined): number {
  if (val === null || val === undefined) return 0;
  return Number(val);
}

// Tipo de coordenadas
interface Coordinate {
  lat: number;
  lng: number;
}

// ============================================
// USUARIOS
// ============================================

export async function getUsers() {
  const users = await prisma.user.findMany({
    include: {
      roles: true,
      settings: true,
    },
    orderBy: { createdAt: "desc" },
  });

  return users.map((u) => ({
    id: u.id,
    name: u.name,
    email: u.email,
    phone: u.phone || undefined,
    dni: u.dni || undefined,
    city: u.city || undefined,
    sector: u.sector || undefined,
    address: u.address || undefined,
    coordinates: u.lat && u.lng ? { lat: decimalToNumber(u.lat), lng: decimalToNumber(u.lng) } : undefined,
    profileImageUrl: u.profileImageUrl || undefined,
    coins: u.coins,
  }));
}

export async function getUserById(id: string) {
  const user = await prisma.user.findUnique({
    where: { id },
    include: {
      roles: true,
      settings: true,
      vendor: true,
      driver: true,
      admin: true,
    },
  });

  if (!user) return null;

  return {
    id: user.id,
    name: user.name,
    email: user.email,
    phone: user.phone || undefined,
    dni: user.dni || undefined,
    roles: user.roles.map((r) => r.role),
    selectedRole: user.settings?.selectedRole || "customer",
    vendor: user.vendor,
    driver: user.driver,
    admin: user.admin,
  };
}

export async function updateUser(id: string, data: Partial<User>) {
  // Verificar si el usuario existe
  const existing = await prisma.user.findUnique({ where: { id } });

  if (existing) {
    // Actualizar usuario existente
    return prisma.user.update({
      where: { id },
      data: {
        name: data.name,
        phone: data.phone,
        dni: data.dni,
        city: data.city,
        sector: data.sector,
        address: data.address,
        lat: data.lat,
        lng: data.lng,
        profileImageUrl: data.profileImageUrl,
        coins: data.coins,
      },
    });
  }

  // Si no existe, crear usuario nuevo
  return prisma.user.create({
    data: {
      id,
      name: data.name || "",
      email: (data as any).email || `${id}@pakiip.com`,
      phone: data.phone,
      dni: data.dni,
      city: data.city,
      sector: data.sector,
      address: data.address,
      lat: data.lat,
      lng: data.lng,
      profileImageUrl: data.profileImageUrl,
      coins: data.coins || 0,
    },
  });
}

export async function deleteUser(id: string) {
  return prisma.user.delete({ where: { id } });
}

// ============================================
// VENDORS (TIENDAS)
// ============================================

export async function getVendors() {
  const vendors = await prisma.vendor.findMany({
    include: {
      products: {
        include: {
          drinks: true,
          variants: true,
        },
      },
      productCategories: true,
      category: true,
      city: true,
    },
    orderBy: { createdAt: "desc" },
  });

  return vendors.map((v) => ({
    id: v.id,
    name: v.name,
    email: v.email,
    phone: v.phone || "",
    dni: v.dni || "",
    description: v.description || "",
    category: v.category?.name || "",
    imageUrl: v.logoUrl || "",
    bannerUrl: v.bannerUrl || undefined,
    address: v.address || "",
    location: v.city?.name || "",
    coordinates: v.lat && v.lng ? { lat: decimalToNumber(v.lat), lng: decimalToNumber(v.lng) } : { lat: 0, lng: 0 },
    isFeatured: v.isFeatured,
    commissionRate: decimalToNumber(v.commissionRate),
    additionalFee: decimalToNumber(v.additionalFee),
    status: v.status,
    paymentMethod: v.paymentMethod,
    bankAccount: v.bankName ? {
      bankName: v.bankName,
      accountNumber: v.bankAccountNumber || "",
      accountHolder: v.bankAccountHolder || "",
    } : undefined,
    qrImageUrl: v.qrImageUrl || undefined,
    qrPaymentName: v.qrPaymentName || undefined,
    ownerId: v.ownerId || undefined,
    products: v.products.map((p) => ({
      id: p.id,
      name: p.name,
      description: p.description || "",
      price: decimalToNumber(p.price),
      costPrice: decimalToNumber(p.costPrice),
      offerPrice: p.offerPrice ? decimalToNumber(p.offerPrice) : undefined,
      isOffer: p.isOffer,
      isFeatured: p.isFeatured,
      imageUrl: p.imageUrl || "",
      vendorId: p.vendorId,
      vendorCategoryId: p.vendorCategoryId || undefined,
      stock: p.stock,
      hasVariants: p.hasVariants || false,
      variants: p.variants?.map((v) => ({
        id: v.id,
        name: v.name,
        price: decimalToNumber(v.price),
        costPrice: decimalToNumber(v.costPrice),
        stock: v.stock,
        isDefault: v.isDefault,
        sortOrder: v.sortOrder,
      })) || [],
      options: {
        packagingFee: p.packagingFee ? decimalToNumber(p.packagingFee) : undefined,
        cutleryPrice: p.cutleryPrice ? decimalToNumber(p.cutleryPrice) : undefined,
        cutleryCostPrice: p.cutleryCostPrice ? decimalToNumber(p.cutleryCostPrice) : undefined,
        drinks: p.drinks.map((d) => ({
          name: d.name,
          price: decimalToNumber(d.price),
          costPrice: decimalToNumber(d.costPrice),
        })),
      },
    })),
    productCategories: v.productCategories.map((c) => ({
      id: c.id,
      name: c.name,
    })),
  }));
}

export async function getVendorById(id: string) {
  const vendor = await prisma.vendor.findUnique({
    where: { id },
    include: {
      products: {
        include: { drinks: true, variants: true },
      },
      productCategories: true,
      category: true,
      city: true,
    },
  });

  if (!vendor) return null;

  // Mismo mapeo que getVendors pero para un solo vendor
  return {
    id: vendor.id,
    name: vendor.name,
    email: vendor.email,
    phone: vendor.phone || "",
    dni: vendor.dni || "",
    description: vendor.description || "",
    category: vendor.category?.name || "",
    imageUrl: vendor.logoUrl || "",
    bannerUrl: vendor.bannerUrl || undefined,
    address: vendor.address || "",
    location: vendor.city?.name || "",
    coordinates: vendor.lat && vendor.lng
      ? { lat: decimalToNumber(vendor.lat), lng: decimalToNumber(vendor.lng) }
      : { lat: 0, lng: 0 },
    isFeatured: vendor.isFeatured,
    commissionRate: decimalToNumber(vendor.commissionRate),
    additionalFee: decimalToNumber(vendor.additionalFee),
    status: vendor.status,
    paymentMethod: vendor.paymentMethod,
    ownerId: vendor.ownerId || undefined,
    products: vendor.products.map((p) => ({
      id: p.id,
      name: p.name,
      description: p.description || "",
      price: decimalToNumber(p.price),
      costPrice: decimalToNumber(p.costPrice),
      offerPrice: p.offerPrice ? decimalToNumber(p.offerPrice) : undefined,
      isOffer: p.isOffer,
      isFeatured: p.isFeatured,
      imageUrl: p.imageUrl || "",
      vendorId: p.vendorId,
      vendorCategoryId: p.vendorCategoryId || undefined,
      stock: p.stock,
      hasVariants: p.hasVariants || false,
      variants: p.variants?.map((v) => ({
        id: v.id,
        name: v.name,
        price: decimalToNumber(v.price),
        costPrice: decimalToNumber(v.costPrice),
        stock: v.stock,
        isDefault: v.isDefault,
        sortOrder: v.sortOrder,
      })) || [],
      options: {
        packagingFee: p.packagingFee ? decimalToNumber(p.packagingFee) : undefined,
        cutleryPrice: p.cutleryPrice ? decimalToNumber(p.cutleryPrice) : undefined,
        cutleryCostPrice: p.cutleryCostPrice ? decimalToNumber(p.cutleryCostPrice) : undefined,
        drinks: p.drinks.map((d) => ({
          name: d.name,
          price: decimalToNumber(d.price),
          costPrice: decimalToNumber(d.costPrice),
        })),
      },
    })),
    productCategories: vendor.productCategories.map((c) => ({
      id: c.id,
      name: c.name,
    })),
  };
}

export async function saveVendor(data: any) {
  const { id, products, productCategories, category, ...vendorData } = data;

  // Buscar ciudad por nombre
  let cityId: string | null = null;
  if (data.location) {
    const city = await prisma.city.findFirst({
      where: { name: data.location },
    });
    cityId = city?.id || null;
  }

  // Buscar categoría por nombre
  let categoryId: string | null = null;
  if (category) {
    const cat = await prisma.category.findFirst({
      where: { name: category },
    });
    categoryId = cat?.id || null;
  }

  const vendorPayload = {
    name: vendorData.name,
    email: vendorData.email,
    phone: vendorData.phone,
    dni: vendorData.dni,
    description: vendorData.description,
    categoryId,
    logoUrl: vendorData.imageUrl,
    bannerUrl: vendorData.bannerUrl,
    address: vendorData.address,
    cityId,
    lat: vendorData.coordinates?.lat,
    lng: vendorData.coordinates?.lng,
    isFeatured: vendorData.isFeatured,
    commissionRate: vendorData.commissionRate,
    additionalFee: vendorData.additionalFee,
    status: vendorData.status as EntityStatus,
    paymentMethod: vendorData.paymentMethod,
    bankName: vendorData.bankAccount?.bankName,
    bankAccountNumber: vendorData.bankAccount?.accountNumber,
    bankAccountHolder: vendorData.bankAccount?.accountHolder,
    qrImageUrl: vendorData.qrImageUrl,
    qrPaymentName: vendorData.qrPaymentName,
    ownerId: vendorData.ownerId,
  };

  // Verificar si el vendor existe en la BD (sin importar el formato del ID)
  const existingVendor = id ? await prisma.vendor.findUnique({ where: { id } }) : null;

  if (existingVendor) {
    // Actualizar vendor existente
    const vendor = await prisma.vendor.update({
      where: { id },
      data: vendorPayload,
    });

    // Actualizar productos
    if (products) {
      // Obtener IDs de productos que vienen del frontend
      const productIdsFromFrontend = products
        .filter((p: any) => p.id && !p.id.startsWith('p')) // Solo IDs reales de la DB (no temporales)
        .map((p: any) => p.id);

      // Obtener productos actuales del vendor en la DB
      const existingProductsInDb = await prisma.product.findMany({
        where: { vendorId: id },
        select: { id: true },
      });

      // Encontrar productos que están en la DB pero no en el frontend (eliminados)
      const productIdsToDelete = existingProductsInDb
        .filter((dbProduct) => !productIdsFromFrontend.includes(dbProduct.id))
        .map((p) => p.id);

      // Eliminar productos que ya no existen en el frontend
      if (productIdsToDelete.length > 0) {
        // Primero eliminar variantes y bebidas asociadas
        await prisma.productVariant.deleteMany({
          where: { productId: { in: productIdsToDelete } },
        });
        await prisma.productDrink.deleteMany({
          where: { productId: { in: productIdsToDelete } },
        });
        // Luego eliminar los productos
        await prisma.product.deleteMany({
          where: { id: { in: productIdsToDelete } },
        });
      }

      for (const product of products) {
        if (product.id) {
          // Verificar si el producto existe en la base de datos
          const existingProduct = await prisma.product.findUnique({
            where: { id: product.id },
          });

          if (existingProduct) {
            // Actualizar producto existente
            await prisma.product.update({
              where: { id: product.id },
              data: {
                name: product.name,
                description: product.description,
                price: product.price,
                costPrice: product.costPrice,
                offerPrice: product.offerPrice,
                isOffer: product.isOffer,
                isFeatured: product.isFeatured,
                imageUrl: product.imageUrl,
                stock: product.stock,
                packagingFee: product.options?.packagingFee,
                cutleryPrice: product.options?.cutleryPrice,
                cutleryCostPrice: product.options?.cutleryCostPrice,
                vendorCategoryId: product.vendorCategoryId || null,
                hasVariants: product.hasVariants || false,
              },
            });

            // Actualizar variantes del producto
            if (product.hasVariants && product.variants && product.variants.length > 0) {
              // Eliminar variantes existentes y crear nuevas
              await prisma.productVariant.deleteMany({
                where: { productId: product.id },
              });
              await prisma.productVariant.createMany({
                data: product.variants.map((v: any, index: number) => ({
                  productId: product.id,
                  name: v.name,
                  price: v.price,
                  costPrice: v.costPrice || 0,
                  stock: v.stock,
                  isDefault: v.isDefault || index === 0,
                  sortOrder: v.sortOrder || index,
                })),
              });
            } else {
              // Si no tiene variantes, eliminar las existentes
              await prisma.productVariant.deleteMany({
                where: { productId: product.id },
              });
            }

            // Actualizar bebidas del producto
            if (product.drinks && product.drinks.length > 0) {
              // Eliminar bebidas existentes y crear nuevas
              await prisma.productDrink.deleteMany({
                where: { productId: product.id },
              });
              await prisma.productDrink.createMany({
                data: product.drinks.map((d: any) => ({
                  productId: product.id,
                  name: d.name,
                  price: d.price,
                  costPrice: d.costPrice || 0,
                })),
              });
            } else {
              // Si no tiene bebidas, eliminar las existentes
              await prisma.productDrink.deleteMany({
                where: { productId: product.id },
              });
            }
          } else {
            // El producto tiene ID pero no existe en DB (ID temporal del frontend)
            // Crear nuevo producto
            const newProduct = await prisma.product.create({
              data: {
                vendorId: id,
                name: product.name,
                description: product.description,
                price: product.price,
                costPrice: product.costPrice,
                offerPrice: product.offerPrice,
                isOffer: product.isOffer,
                isFeatured: product.isFeatured,
                imageUrl: product.imageUrl,
                stock: product.stock,
                packagingFee: product.options?.packagingFee,
                cutleryPrice: product.options?.cutleryPrice,
                cutleryCostPrice: product.options?.cutleryCostPrice,
                vendorCategoryId: product.vendorCategoryId || null,
                hasVariants: product.hasVariants || false,
              },
            });

            // Crear variantes si existen
            if (product.hasVariants && product.variants && product.variants.length > 0) {
              await prisma.productVariant.createMany({
                data: product.variants.map((v: any, index: number) => ({
                  productId: newProduct.id,
                  name: v.name,
                  price: v.price,
                  costPrice: v.costPrice || 0,
                  stock: v.stock,
                  isDefault: v.isDefault || index === 0,
                  sortOrder: v.sortOrder || index,
                })),
              });
            }

            // Crear bebidas si existen
            if (product.drinks && product.drinks.length > 0) {
              await prisma.productDrink.createMany({
                data: product.drinks.map((d: any) => ({
                  productId: newProduct.id,
                  name: d.name,
                  price: d.price,
                  costPrice: d.costPrice || 0,
                })),
              });
            }
          }
        } else {
          // Crear nuevo producto (sin ID)
          const newProduct = await prisma.product.create({
            data: {
              vendorId: id,
              name: product.name,
              description: product.description,
              price: product.price,
              costPrice: product.costPrice,
              offerPrice: product.offerPrice,
              isOffer: product.isOffer,
              isFeatured: product.isFeatured,
              imageUrl: product.imageUrl,
              stock: product.stock,
              packagingFee: product.options?.packagingFee,
              cutleryPrice: product.options?.cutleryPrice,
              cutleryCostPrice: product.options?.cutleryCostPrice,
              vendorCategoryId: product.vendorCategoryId || null,
              hasVariants: product.hasVariants || false,
            },
          });

          // Crear variantes si existen
          if (product.hasVariants && product.variants && product.variants.length > 0) {
            await prisma.productVariant.createMany({
              data: product.variants.map((v: any, index: number) => ({
                productId: newProduct.id,
                name: v.name,
                price: v.price,
                costPrice: v.costPrice || 0,
                stock: v.stock,
                isDefault: v.isDefault || index === 0,
                sortOrder: v.sortOrder || index,
              })),
            });
          }

          // Crear bebidas si existen
          if (product.drinks && product.drinks.length > 0) {
            await prisma.productDrink.createMany({
              data: product.drinks.map((d: any) => ({
                productId: newProduct.id,
                name: d.name,
                price: d.price,
                costPrice: d.costPrice || 0,
              })),
            });
          }
        }
      }
    }

    // Actualizar categorías de productos del vendor
    if (productCategories) {
      // Obtener IDs de categorías que vienen del frontend (solo las reales de la DB)
      const categoryIdsFromFrontend = productCategories
        .filter((c: any) => c.id && !c.id.startsWith('cat') && c.id.startsWith('cm'))
        .map((c: any) => c.id);

      // Obtener categorías actuales del vendor en la DB
      const existingCategoriesInDb = await prisma.vendorProductCategory.findMany({
        where: { vendorId: id },
        select: { id: true },
      });

      // Encontrar categorías a eliminar
      const categoryIdsToDelete = existingCategoriesInDb
        .filter((dbCat) => !categoryIdsFromFrontend.includes(dbCat.id))
        .map((c) => c.id);

      // Eliminar categorías que ya no existen en el frontend
      if (categoryIdsToDelete.length > 0) {
        await prisma.vendorProductCategory.deleteMany({
          where: { id: { in: categoryIdsToDelete } },
        });
      }

      // Crear o actualizar categorías
      for (const cat of productCategories) {
        const isTemporaryCatId = cat.id && (cat.id.startsWith('cat') || !cat.id.startsWith('cm'));

        if (cat.id && !isTemporaryCatId) {
          // Verificar si existe
          const existingCat = await prisma.vendorProductCategory.findUnique({ where: { id: cat.id } });
          if (existingCat) {
            await prisma.vendorProductCategory.update({
              where: { id: cat.id },
              data: { name: cat.name },
            });
          } else {
            // No existe, crear nueva
            await prisma.vendorProductCategory.create({
              data: { vendorId: id, name: cat.name },
            });
          }
        } else {
          // ID temporal o sin ID, crear nueva
          await prisma.vendorProductCategory.create({
            data: { vendorId: id, name: cat.name },
          });
        }
      }
    }

    return vendor;
  } else {
    // Crear nuevo vendor
    const newVendor = await prisma.vendor.create({
      data: vendorPayload,
    });

    // Crear productos del nuevo vendor
    if (products && products.length > 0) {
      for (const product of products) {
        const newProduct = await prisma.product.create({
          data: {
            vendorId: newVendor.id,
            name: product.name,
            description: product.description,
            price: product.price,
            costPrice: product.costPrice,
            offerPrice: product.offerPrice,
            isOffer: product.isOffer,
            isFeatured: product.isFeatured,
            imageUrl: product.imageUrl,
            stock: product.stock,
            packagingFee: product.options?.packagingFee,
            cutleryPrice: product.options?.cutleryPrice,
            cutleryCostPrice: product.options?.cutleryCostPrice,
            vendorCategoryId: product.vendorCategoryId || null,
            hasVariants: product.hasVariants || false,
          },
        });

        // Crear variantes si existen
        if (product.hasVariants && product.variants && product.variants.length > 0) {
          await prisma.productVariant.createMany({
            data: product.variants.map((v: any, index: number) => ({
              productId: newProduct.id,
              name: v.name,
              price: v.price,
              costPrice: v.costPrice || 0,
              stock: v.stock,
              isDefault: v.isDefault || index === 0,
              sortOrder: v.sortOrder || index,
            })),
          });
        }

        // Crear bebidas si existen
        if (product.drinks && product.drinks.length > 0) {
          await prisma.productDrink.createMany({
            data: product.drinks.map((d: any) => ({
              productId: newProduct.id,
              name: d.name,
              price: d.price,
              costPrice: d.costPrice || 0,
            })),
          });
        }
      }
    }

    // Crear categorías de productos para el nuevo vendor
    if (productCategories && productCategories.length > 0) {
      for (const cat of productCategories) {
        await prisma.vendorProductCategory.create({
          data: {
            vendorId: newVendor.id,
            name: cat.name,
          },
        });
      }
    }

    return newVendor;
  }
}

export async function deleteVendor(id: string) {
  return prisma.vendor.delete({
    where: { id },
  });
}

// ============================================
// ORDERS (PEDIDOS)
// ============================================

export async function getOrders(filters?: {
  userId?: string;
  driverId?: string;
  status?: OrderStatus;
}) {
  const where: any = {};

  if (filters?.userId) where.userId = filters.userId;
  if (filters?.driverId) where.driverId = filters.driverId;
  if (filters?.status) where.status = filters.status;

  const orders = await prisma.order.findMany({
    where,
    include: {
      items: {
        include: {
          vendor: true,
          product: true,
        },
      },
      user: true,
      driver: true,
    },
    orderBy: { createdAt: "desc" },
  });

  return orders.map((o) => ({
    id: o.id,
    date: o.createdAt.toISOString(),
    userId: o.userId || undefined,
    customerName: o.customerName,
    customerPhone: o.customerPhone || "",
    customerAddress: o.customerAddress,
    customerCoordinates: o.customerLat && o.customerLng
      ? { lat: decimalToNumber(o.customerLat), lng: decimalToNumber(o.customerLng) }
      : undefined,
    status: o.status,
    total: decimalToNumber(o.total),
    shippingFee: decimalToNumber(o.shippingFee),
    paymentMethod: o.paymentMethod || "",
    paymentProofUrl: o.paymentProofUrl || undefined,
    driverId: o.driverId || undefined,
    driverPayoutStatus: o.driverPayoutStatus,
    verificationCode: o.verificationCode || "",
    items: o.items.map((i) => ({
      productId: i.productId || "",
      productName: i.productName,
      vendor: i.vendorName,
      quantity: i.quantity,
      price: decimalToNumber(i.price),
      costPrice: decimalToNumber(i.costPrice),
      payoutStatus: i.payoutStatus,
      options: {
        cutlery: i.hasCutlery || false,
        drink: i.drinkName || undefined,
        cutleryPrice: i.cutleryPrice ? decimalToNumber(i.cutleryPrice) : undefined,
        cutleryCostPrice: i.cutleryCostPrice ? decimalToNumber(i.cutleryCostPrice) : undefined,
        drinkPrice: i.drinkPrice ? decimalToNumber(i.drinkPrice) : undefined,
        drinkCostPrice: i.drinkCostPrice ? decimalToNumber(i.drinkCostPrice) : undefined,
        variant: i.variantName ? {
          name: i.variantName,
          price: i.variantPrice ? decimalToNumber(i.variantPrice) : undefined,
        } : undefined,
      },
    })),
  }));
}

export async function saveOrder(data: any) {
  const { id, items, ...orderData } = data;

  // Convertir status del frontend al enum
  const statusMap: Record<string, OrderStatus> = {
    "Procesando": "Procesando",
    "Listo para Recoger": "ListoParaRecoger",
    "Esperando Aceptación": "EsperandoAceptacion",
    "Enviado": "Enviado",
    "Entregado": "Entregado",
    "Cancelado": "Cancelado",
  };

  const orderPayload = {
    userId: orderData.userId || orderData.customerId, // Aceptar userId o customerId
    driverId: orderData.driverId,
    customerName: orderData.customerName,
    customerPhone: orderData.customerPhone,
    customerAddress: orderData.customerAddress,
    customerLat: orderData.customerCoordinates?.lat,
    customerLng: orderData.customerCoordinates?.lng,
    status: statusMap[orderData.status] || "Procesando",
    total: orderData.total,
    shippingFee: orderData.shippingFee,
    paymentMethod: orderData.paymentMethod,
    paymentProofUrl: orderData.paymentProofUrl,
    driverPayoutStatus: orderData.driverPayoutStatus,
    verificationCode: orderData.verificationCode,
  };

  if (id) {
    // Verificar si la orden ya existe en la base de datos
    const existingOrder = await prisma.order.findUnique({ where: { id } });

    if (existingOrder) {
      // Update orden existente
      return prisma.order.update({
        where: { id },
        data: orderPayload,
      });
    } else {
      // Crear orden nueva con el ID proporcionado
      return prisma.order.create({
        data: {
          id,
          ...orderPayload,
          items: items ? {
            create: items.map((i: any) => ({
              productId: i.productId,
              vendorId: i.vendorId,
              productName: i.productName,
              vendorName: i.vendor,
              quantity: i.quantity,
              price: i.price,
              costPrice: i.costPrice,
              payoutStatus: i.payoutStatus,
              hasCutlery: i.options?.cutlery || false,
              cutleryPrice: i.options?.cutleryPrice || null,
              cutleryCostPrice: i.options?.cutleryCostPrice || null,
              drinkName: i.options?.drink || null,
              drinkPrice: i.options?.drinkPrice || null,
              drinkCostPrice: i.options?.drinkCostPrice || null,
              variantName: i.options?.variant?.name || null,
              variantPrice: i.options?.variant?.price || null,
            })),
          } : undefined,
        },
      });
    }
  } else {
    const orderId = `ORD-${Date.now()}`;
    return prisma.order.create({
      data: {
        id: orderId,
        ...orderPayload,
        items: items ? {
          create: items.map((i: any) => ({
            productId: i.productId,
            vendorId: i.vendorId,
            productName: i.productName,
            vendorName: i.vendor,
            quantity: i.quantity,
            price: i.price,
            costPrice: i.costPrice,
            payoutStatus: i.payoutStatus,
            hasCutlery: i.options?.cutlery || false,
            cutleryPrice: i.options?.cutleryPrice || null,
            cutleryCostPrice: i.options?.cutleryCostPrice || null,
            drinkName: i.options?.drink || null,
            drinkPrice: i.options?.drinkPrice || null,
            drinkCostPrice: i.options?.drinkCostPrice || null,
            variantName: i.options?.variant?.name || null,
            variantPrice: i.options?.variant?.price || null,
          })),
        } : undefined,
      },
    });
  }
}

export async function getOrderById(id: string) {
  const order = await prisma.order.findUnique({
    where: { id },
    include: {
      items: {
        include: {
          vendor: true,
          product: true,
        },
      },
      user: true,
      driver: true,
    },
  });

  if (!order) return null;

  return {
    id: order.id,
    date: order.createdAt.toISOString(),
    userId: order.userId || undefined,
    customerName: order.customerName,
    customerPhone: order.customerPhone || "",
    customerAddress: order.customerAddress,
    customerCoordinates: order.customerLat && order.customerLng
      ? { lat: decimalToNumber(order.customerLat), lng: decimalToNumber(order.customerLng) }
      : undefined,
    status: order.status,
    total: decimalToNumber(order.total),
    shippingFee: decimalToNumber(order.shippingFee),
    paymentMethod: order.paymentMethod || "",
    paymentProofUrl: order.paymentProofUrl || undefined,
    driverId: order.driverId || undefined,
    driverPayoutStatus: order.driverPayoutStatus,
    verificationCode: order.verificationCode || "",
    items: order.items.map((i) => ({
      productId: i.productId || "",
      productName: i.productName,
      vendor: i.vendorName,
      quantity: i.quantity,
      price: decimalToNumber(i.price),
      costPrice: decimalToNumber(i.costPrice),
      payoutStatus: i.payoutStatus,
      options: {
        cutlery: i.hasCutlery || false,
        drink: i.drinkName || undefined,
        cutleryPrice: i.cutleryPrice ? decimalToNumber(i.cutleryPrice) : undefined,
        cutleryCostPrice: i.cutleryCostPrice ? decimalToNumber(i.cutleryCostPrice) : undefined,
        drinkPrice: i.drinkPrice ? decimalToNumber(i.drinkPrice) : undefined,
        drinkCostPrice: i.drinkCostPrice ? decimalToNumber(i.drinkCostPrice) : undefined,
        variant: i.variantName ? {
          name: i.variantName,
          price: i.variantPrice ? decimalToNumber(i.variantPrice) : undefined,
        } : undefined,
      },
    })),
  };
}

export async function deleteOrder(id: string) {
  // Primero eliminar los items de la orden
  await prisma.orderItem.deleteMany({ where: { orderId: id } });
  return prisma.order.delete({ where: { id } });
}

// ============================================
// DRIVERS (REPARTIDORES)
// ============================================

export async function getDrivers() {
  const drivers = await prisma.driver.findMany({
    include: {
      debtTransactions: {
        orderBy: { createdAt: "desc" },
      },
    },
    orderBy: { createdAt: "desc" },
  });

  return drivers.map((d) => ({
    id: d.id,
    userId: d.userId || undefined,
    name: d.name,
    email: d.email,
    dni: d.dni || "",
    phone: d.phone || "",
    vehicle: d.vehicle,
    status: d.status,
    commissionRate: decimalToNumber(d.commissionRate),
    debt: decimalToNumber(d.debt),
    address: d.address || undefined,
    city: d.city || undefined,
    sector: d.sector || undefined,
    coordinates: d.lat && d.lng
      ? { lat: decimalToNumber(d.lat), lng: decimalToNumber(d.lng) }
      : undefined,
    documentImageUrl: d.documentImageUrl || undefined,
    profileImageUrl: d.profileImageUrl || undefined,
    paymentMethod: d.paymentMethod,
    bankAccount: d.bankAccount || undefined,
    qrImageUrl: d.qrImageUrl || undefined,
    qrPaymentName: d.qrPaymentName || undefined,
    rating: d.rating ? decimalToNumber(d.rating) : undefined,
    deliveries: d.deliveries,
    debtTransactions: d.debtTransactions.map((t) => ({
      id: t.id,
      date: t.createdAt.toISOString(),
      type: t.type,
      amount: decimalToNumber(t.amount),
      description: t.description || "",
    })),
  }));
}

export async function saveDriver(data: any) {
  const { id, debtTransactions, ...driverData } = data;

  const driverPayload = {
    name: driverData.name,
    email: driverData.email,
    dni: driverData.dni,
    phone: driverData.phone,
    vehicle: driverData.vehicle as VehicleType,
    status: driverData.status as EntityStatus,
    commissionRate: driverData.commissionRate,
    debt: driverData.debt,
    address: driverData.address,
    city: driverData.city,
    sector: driverData.sector,
    lat: driverData.coordinates?.lat,
    lng: driverData.coordinates?.lng,
    documentImageUrl: driverData.documentImageUrl,
    profileImageUrl: driverData.profileImageUrl,
    paymentMethod: driverData.paymentMethod,
    bankAccount: driverData.bankAccount,
    qrImageUrl: driverData.qrImageUrl,
    qrPaymentName: driverData.qrPaymentName,
    rating: driverData.rating,
    deliveries: driverData.deliveries,
    userId: driverData.userId,
  };

  // Verificar si el driver existe en la BD (sin importar el formato del ID)
  const existing = id ? await prisma.driver.findUnique({ where: { id } }) : null;

  if (existing) {
    return prisma.driver.update({
      where: { id },
      data: driverPayload,
    });
  }

  // Sin ID o no existe: crear nuevo driver
  return prisma.driver.create({
    data: driverPayload,
  });
}

export async function getDriverById(id: string) {
  const driver = await prisma.driver.findUnique({
    where: { id },
    include: {
      debtTransactions: {
        orderBy: { createdAt: "desc" },
      },
    },
  });

  if (!driver) return null;

  return {
    id: driver.id,
    userId: driver.userId || undefined,
    name: driver.name,
    email: driver.email,
    dni: driver.dni || "",
    phone: driver.phone || "",
    vehicle: driver.vehicle,
    status: driver.status,
    commissionRate: decimalToNumber(driver.commissionRate),
    debt: decimalToNumber(driver.debt),
    address: driver.address || undefined,
    city: driver.city || undefined,
    sector: driver.sector || undefined,
    coordinates: driver.lat && driver.lng
      ? { lat: decimalToNumber(driver.lat), lng: decimalToNumber(driver.lng) }
      : undefined,
    documentImageUrl: driver.documentImageUrl || undefined,
    profileImageUrl: driver.profileImageUrl || undefined,
    paymentMethod: driver.paymentMethod,
    bankAccount: driver.bankAccount || undefined,
    qrImageUrl: driver.qrImageUrl || undefined,
    qrPaymentName: driver.qrPaymentName || undefined,
    rating: driver.rating ? decimalToNumber(driver.rating) : undefined,
    deliveries: driver.deliveries,
    debtTransactions: driver.debtTransactions.map((t) => ({
      id: t.id,
      date: t.createdAt.toISOString(),
      type: t.type,
      amount: decimalToNumber(t.amount),
      description: t.description || "",
    })),
  };
}

export async function deleteDriver(id: string) {
  // Primero eliminar transacciones de deuda
  await prisma.driverDebtTransaction.deleteMany({ where: { driverId: id } });
  return prisma.driver.delete({ where: { id } });
}

// ============================================
// CATEGORÍAS
// ============================================

export async function getCategories() {
  const categories = await prisma.category.findMany({
    orderBy: { name: "asc" },
  });

  return categories.map((c) => ({
    id: c.id,
    name: c.name,
    imageUrl: c.imageUrl || "",
    imageHint: c.imageHint || "",
  }));
}

export async function saveCategory(data: any) {
  const { id, ...categoryData } = data;

  // Verificar si la categoría existe en la BD (sin importar el formato del ID)
  const existing = id ? await prisma.category.findUnique({ where: { id } }) : null;

  if (existing) {
    return prisma.category.update({
      where: { id },
      data: categoryData,
    });
  }

  // Sin ID o no existe: crear nueva categoría
  return prisma.category.create({
    data: categoryData,
  });
}

export async function deleteCategory(id: string) {
  return prisma.category.delete({ where: { id } });
}

// ============================================
// CIUDADES
// ============================================

export async function getCities() {
  const cities = await prisma.city.findMany({
    include: {
      sectors: true,
    },
    orderBy: { name: "asc" },
  });

  return cities.map((c) => ({
    id: c.id,
    name: c.name,
    coordinates: c.lat && c.lng
      ? { lat: decimalToNumber(c.lat), lng: decimalToNumber(c.lng) }
      : { lat: 0, lng: 0 },
    sectors: c.sectors.map((s) => ({
      name: s.name,
      fee: decimalToNumber(s.fee),
    })),
  }));
}

export async function saveCity(data: any) {
  const { id, sectors, ...cityData } = data;

  const cityPayload = {
    name: cityData.name,
    lat: cityData.coordinates?.lat,
    lng: cityData.coordinates?.lng,
  };

  // Verificar si es un ID temporal o real de la DB
  const isTemporaryId = id && (id.startsWith('city') || !id.startsWith('cm'));

  if (id && !isTemporaryId) {
    // Verificar si existe antes de actualizar
    const existing = await prisma.city.findUnique({ where: { id } });

    if (existing) {
      // Actualizar ciudad
      const city = await prisma.city.update({
        where: { id },
        data: cityPayload,
      });

      // Actualizar sectores
      if (sectors) {
        // Eliminar sectores existentes y recrear
        await prisma.citySector.deleteMany({ where: { cityId: id } });
        if (sectors.length > 0) {
          await prisma.citySector.createMany({
            data: sectors.map((s: any) => ({
              cityId: id,
              name: s.name,
              fee: s.fee || 0,
            })),
          });
        }
      }

      return city;
    }
  }

  // Crear nueva ciudad
  const newCity = await prisma.city.create({
    data: cityPayload,
  });

  // Crear sectores si existen
  if (sectors && sectors.length > 0) {
    await prisma.citySector.createMany({
      data: sectors.map((s: any) => ({
        cityId: newCity.id,
        name: s.name,
        fee: s.fee || 0,
      })),
    });
  }

  return newCity;
}

export async function deleteCity(id: string) {
  // Primero eliminar sectores
  await prisma.citySector.deleteMany({ where: { cityId: id } });
  return prisma.city.delete({ where: { id } });
}

// ============================================
// APP SETTINGS
// ============================================

export async function getAppSettings() {
  let settings = await prisma.appSettings.findFirst();

  if (!settings) {
    settings = await prisma.appSettings.create({
      data: { id: 1 },
    });
  }

  // Parsear JSON fields con valores por defecto
  const paymentMethods = (settings as any).paymentMethods || {};
  const promotionalBanners = (settings as any).promotionalBanners || [];
  const announcementBanners = (settings as any).announcementBanners || [];

  // Parsear shippingSettings - usar mismo patrón que paymentMethods (nunca devolver null)
  const shippingSettings = (settings as any).shippingSettings || {};

  return {
    appName: settings.appName,
    logoUrl: settings.logoUrl || "",
    heroImageUrl: settings.heroImageUrl || "",
    welcomeImageUrl: settings.welcomeImageUrl || "",
    driverWelcomeImageUrl: settings.driverWelcomeImageUrl || "",
    loginBackgroundImageUrl: settings.loginBackgroundImageUrl || undefined,
    hideFooter: settings.hideFooter,
    taxType: settings.taxType,
    taxRate: decimalToNumber(settings.taxRate),
    taxExemptRegions: settings.taxExemptRegions as string[],
    currencySymbol: settings.currencySymbol,
    verificationMethods: settings.verificationMethods,
    enablePasswordRecovery: settings.enablePasswordRecovery,
    featuredStoreCost: decimalToNumber(settings.featuredStoreCost),
    customDomain: settings.customDomain || undefined,
    googleClientId: (settings as any).googleClientId || undefined,
    // Campos JSON complejos
    shipping: shippingSettings,
    paymentMethods: paymentMethods,
    promotionalBanners: promotionalBanners,
    announcementBanners: announcementBanners,
    // Campos legacy para compatibilidad
    shippingSettings: shippingSettings,
    cashOnDeliveryEnabled: settings.cashOnDeliveryEnabled,
  };
}

export async function saveAppSettings(data: any) {
  // Mapear campos del frontend a campos de Prisma
  const updateData: any = {};

  // Campos simples
  if (data.appName !== undefined) updateData.appName = data.appName;
  if (data.logoUrl !== undefined) updateData.logoUrl = data.logoUrl;
  if (data.heroImageUrl !== undefined) updateData.heroImageUrl = data.heroImageUrl;
  if (data.welcomeImageUrl !== undefined) updateData.welcomeImageUrl = data.welcomeImageUrl;
  if (data.driverWelcomeImageUrl !== undefined) updateData.driverWelcomeImageUrl = data.driverWelcomeImageUrl;
  if (data.loginBackgroundImageUrl !== undefined) updateData.loginBackgroundImageUrl = data.loginBackgroundImageUrl;
  if (data.hideFooter !== undefined) updateData.hideFooter = data.hideFooter;
  if (data.taxType !== undefined) updateData.taxType = data.taxType;
  if (data.taxRate !== undefined) updateData.taxRate = data.taxRate;
  if (data.taxExemptRegions !== undefined) updateData.taxExemptRegions = data.taxExemptRegions;
  if (data.currencySymbol !== undefined) updateData.currencySymbol = data.currencySymbol;
  if (data.verificationMethods !== undefined) updateData.verificationMethods = data.verificationMethods;
  if (data.enablePasswordRecovery !== undefined) updateData.enablePasswordRecovery = data.enablePasswordRecovery;
  if (data.featuredStoreCost !== undefined) updateData.featuredStoreCost = data.featuredStoreCost;
  if (data.customDomain !== undefined) updateData.customDomain = data.customDomain;
  if (data.googleClientId !== undefined) updateData.googleClientId = data.googleClientId;

  // Campos JSON complejos
  if (data.shipping !== undefined) updateData.shippingSettings = data.shipping;
  if (data.shippingSettings !== undefined) updateData.shippingSettings = data.shippingSettings;
  if (data.paymentMethods !== undefined) updateData.paymentMethods = data.paymentMethods;
  if (data.promotionalBanners !== undefined) updateData.promotionalBanners = data.promotionalBanners;
  if (data.announcementBanners !== undefined) updateData.announcementBanners = data.announcementBanners;

  // cashOnDeliveryEnabled puede venir directo o dentro de paymentMethods
  if (data.cashOnDeliveryEnabled !== undefined) {
    updateData.cashOnDeliveryEnabled = data.cashOnDeliveryEnabled;
  } else if (data.paymentMethods?.cashOnDeliveryEnabled !== undefined) {
    updateData.cashOnDeliveryEnabled = data.paymentMethods.cashOnDeliveryEnabled;
  }

  return prisma.appSettings.upsert({
    where: { id: 1 },
    update: updateData,
    create: { id: 1, ...updateData },
  });
}

// ============================================
// BANNERS
// ============================================

export async function getPromotionalBanners() {
  const banners = await prisma.promotionalBanner.findMany({
    where: { isActive: true },
    orderBy: { createdAt: "desc" },
  });

  return banners.map((b) => ({
    id: b.id,
    imageUrl: b.imageUrl,
    title: b.title || "",
    description: b.description || "",
    link: b.link || "",
    imageHint: b.imageHint || "",
    locations: b.locations as string[],
  }));
}

export async function getAnnouncementBanners() {
  const banners = await prisma.announcementBanner.findMany({
    where: { isActive: true },
    orderBy: { createdAt: "desc" },
  });

  return banners.map((b) => ({
    id: b.id,
    imageUrl: b.imageUrl,
    title: b.title || "",
    description: b.description || "",
    link: b.link || "",
    imageHint: b.imageHint || "",
    locations: b.locations as string[],
  }));
}

export async function savePromotionalBanner(data: any) {
  const { id, ...bannerData } = data;

  const bannerPayload = {
    imageUrl: bannerData.imageUrl,
    title: bannerData.title,
    description: bannerData.description,
    link: bannerData.link,
    imageHint: bannerData.imageHint,
    locations: bannerData.locations || [],
    isActive: bannerData.isActive !== false,
  };

  const isTemporaryId = id && (id.startsWith('banner') || !id.startsWith('cm'));

  if (id && !isTemporaryId) {
    const existing = await prisma.promotionalBanner.findUnique({ where: { id } });
    if (existing) {
      return prisma.promotionalBanner.update({
        where: { id },
        data: bannerPayload,
      });
    }
  }

  return prisma.promotionalBanner.create({ data: bannerPayload });
}

export async function deletePromotionalBanner(id: string) {
  return prisma.promotionalBanner.delete({ where: { id } });
}

export async function saveAnnouncementBanner(data: any) {
  const { id, ...bannerData } = data;

  const bannerPayload = {
    imageUrl: bannerData.imageUrl,
    title: bannerData.title,
    description: bannerData.description,
    link: bannerData.link,
    imageHint: bannerData.imageHint,
    locations: bannerData.locations || [],
    isActive: bannerData.isActive !== false,
  };

  const isTemporaryId = id && (id.startsWith('banner') || !id.startsWith('cm'));

  if (id && !isTemporaryId) {
    const existing = await prisma.announcementBanner.findUnique({ where: { id } });
    if (existing) {
      return prisma.announcementBanner.update({
        where: { id },
        data: bannerPayload,
      });
    }
  }

  return prisma.announcementBanner.create({ data: bannerPayload });
}

export async function deleteAnnouncementBanner(id: string) {
  return prisma.announcementBanner.delete({ where: { id } });
}

// ============================================
// BANK ACCOUNTS Y QR
// ============================================

export async function getBankAccounts() {
  return prisma.bankAccount.findMany();
}

export async function saveBankAccount(data: any) {
  const { id, ...accountData } = data;

  const accountPayload = {
    bankName: accountData.bankName,
    accountNumber: accountData.accountNumber,
    accountHolder: accountData.accountHolder,
    accountType: accountData.accountType,
  };

  const isTemporaryId = id && (id.startsWith('bank') || !id.startsWith('cm'));

  if (id && !isTemporaryId) {
    const existing = await prisma.bankAccount.findUnique({ where: { id } });
    if (existing) {
      return prisma.bankAccount.update({
        where: { id },
        data: accountPayload,
      });
    }
  }

  return prisma.bankAccount.create({ data: accountPayload });
}

export async function deleteBankAccount(id: string) {
  return prisma.bankAccount.delete({ where: { id } });
}

export async function getQrPayments() {
  return prisma.qrPayment.findMany();
}

export async function saveQrPayment(data: any) {
  const { id, ...qrData } = data;

  const qrPayload = {
    name: qrData.name,
    imageUrl: qrData.imageUrl,
  };

  const isTemporaryId = id && (id.startsWith('qr') || !id.startsWith('cm'));

  if (id && !isTemporaryId) {
    const existing = await prisma.qrPayment.findUnique({ where: { id } });
    if (existing) {
      return prisma.qrPayment.update({
        where: { id },
        data: qrPayload,
      });
    }
  }

  return prisma.qrPayment.create({ data: qrPayload });
}

export async function deleteQrPayment(id: string) {
  return prisma.qrPayment.delete({ where: { id } });
}

// ============================================
// FAVORS (ENCARGOS)
// ============================================

export async function getFavors(userId?: string) {
  const where = userId ? { userId } : {};

  const favors = await prisma.favor.findMany({
    where,
    include: {
      driver: true,
    },
    orderBy: { createdAt: "desc" },
  });

  return favors.map((f) => ({
    id: f.id,
    date: f.createdAt.toISOString(),
    userName: f.userName || "",
    description: f.description,
    pickupAddress: f.pickupAddress,
    deliveryAddress: f.deliveryAddress,
    pickupLocation: f.pickupLat && f.pickupLng
      ? { lat: decimalToNumber(f.pickupLat), lng: decimalToNumber(f.pickupLng) }
      : { lat: 0, lng: 0 },
    deliveryLocation: f.deliveryLat && f.deliveryLng
      ? { lat: decimalToNumber(f.deliveryLat), lng: decimalToNumber(f.deliveryLng) }
      : { lat: 0, lng: 0 },
    estimatedProductCost: f.estimatedProductCost
      ? decimalToNumber(f.estimatedProductCost)
      : 0,
    photoDataUri: f.photoUrl || undefined,
    quote: f.quoteData,
    status: f.status,
    driverId: f.driverId || undefined,
  }));
}

export async function saveFavor(data: any) {
  const { id, ...favorData } = data;

  const favorPayload = {
    userId: favorData.userId,
    userName: favorData.userName,
    description: favorData.description,
    pickupAddress: favorData.pickupAddress,
    deliveryAddress: favorData.deliveryAddress,
    pickupLat: favorData.pickupLocation?.lat,
    pickupLng: favorData.pickupLocation?.lng,
    deliveryLat: favorData.deliveryLocation?.lat,
    deliveryLng: favorData.deliveryLocation?.lng,
    estimatedProductCost: favorData.estimatedProductCost,
    photoUrl: favorData.photoDataUri,
    quoteData: favorData.quote,
    status: favorData.status || "Pendiente",
    driverId: favorData.driverId,
  };

  // Verificar si es un ID temporal o real de la DB
  const isTemporaryId = id && (id.startsWith('favor') || id.startsWith('FAV') || !id.startsWith('cm'));

  if (id && !isTemporaryId) {
    // Verificar si existe antes de actualizar
    const existing = await prisma.favor.findUnique({ where: { id } });

    if (existing) {
      return prisma.favor.update({
        where: { id },
        data: favorPayload,
      });
    }
  }

  // Sin ID, ID temporal o no existe: crear nuevo favor
  const favorId = `FAV-${Date.now()}`;
  return prisma.favor.create({
    data: {
      id: favorId,
      ...favorPayload,
    },
  });
}

export async function deleteFavor(id: string) {
  return prisma.favor.delete({
    where: { id },
  });
}

// ============================================
// ADMINS
// ============================================

export async function getAdmins() {
  const admins = await prisma.admin.findMany({
    include: { user: true },
  });

  return admins.map((a) => ({
    id: a.id,
    name: a.name,
    email: a.email,
    phone: a.phone || "",
    permissions: a.permissions as string[],
  }));
}

export async function saveAdmin(data: any) {
  const { id, ...adminData } = data;

  const adminPayload = {
    name: adminData.name,
    email: adminData.email,
    phone: adminData.phone,
    permissions: adminData.permissions || [],
    userId: adminData.userId,
  };

  // Verificar si es un ID temporal o real de la DB
  const isTemporaryId = id && (id.startsWith('admin') || id.startsWith('a') || !id.startsWith('cm'));

  if (id && !isTemporaryId) {
    // Verificar si existe antes de actualizar
    const existing = await prisma.admin.findUnique({ where: { id } });

    if (existing) {
      return prisma.admin.update({
        where: { id },
        data: adminPayload,
      });
    }
  }

  // Sin ID, ID temporal o no existe: crear nuevo admin
  return prisma.admin.create({
    data: adminPayload,
  });
}

export async function deleteAdmin(id: string) {
  return prisma.admin.delete({
    where: { id },
  });
}

// ============================================
// DELIVERY ZONES
// ============================================

export async function getDeliveryZones() {
  const zones = await prisma.deliveryZone.findMany({
    include: { city: true },
  });

  return zones.map((z) => ({
    id: z.id,
    name: z.name,
    cityId: z.cityId,
    path: (z.path as unknown) as Coordinate[],
    shippingFee: decimalToNumber(z.shippingFee),
  }));
}

export async function saveDeliveryZone(data: any) {
  const { id, ...zoneData } = data;

  const zonePayload = {
    name: zoneData.name,
    cityId: zoneData.cityId,
    path: zoneData.path,
    shippingFee: zoneData.shippingFee,
  };

  // Verificar si es un ID temporal o real de la DB
  const isTemporaryId = id && (id.startsWith('zone') || !id.startsWith('cm'));

  if (id && !isTemporaryId) {
    const existing = await prisma.deliveryZone.findUnique({ where: { id } });

    if (existing) {
      return prisma.deliveryZone.update({
        where: { id },
        data: zonePayload,
      });
    }
  }

  return prisma.deliveryZone.create({
    data: zonePayload,
  });
}

export async function deleteDeliveryZone(id: string) {
  return prisma.deliveryZone.delete({ where: { id } });
}

// ============================================
// CARTS
// ============================================

export async function getCart(userId: string) {
  const cart = await prisma.cart.findUnique({
    where: { userId },
  });

  return cart ? {
    userId: cart.userId,
    items: cart.items,
    lastUpdated: cart.updatedAt.toISOString(),
  } : null;
}

export async function saveCart(userId: string, items: any[]) {
  return prisma.cart.upsert({
    where: { userId },
    update: { items },
    create: { userId, items },
  });
}

export async function deleteCart(userId: string) {
  return prisma.cart.delete({ where: { userId } });
}

// ============================================
// REWARDS (RECOMPENSAS)
// ============================================

export async function getRewards() {
  const rewards = await prisma.reward.findMany({
    orderBy: { createdAt: "desc" },
  });

  return rewards.map((r) => ({
    id: r.id,
    name: r.name,
    description: r.description || "",
    imageUrl: r.imageUrl || "",
    coinsCost: r.coinsCost,
    stock: r.stock,
    isActive: r.isActive,
  }));
}

export async function getRewardById(id: string) {
  const reward = await prisma.reward.findUnique({ where: { id } });

  if (!reward) return null;

  return {
    id: reward.id,
    name: reward.name,
    description: reward.description || "",
    imageUrl: reward.imageUrl || "",
    coinsCost: reward.coinsCost,
    stock: reward.stock,
    isActive: reward.isActive,
  };
}

export async function saveReward(data: any) {
  const { id, ...rewardData } = data;

  const rewardPayload = {
    name: rewardData.name,
    description: rewardData.description,
    imageUrl: rewardData.imageUrl,
    coinsCost: rewardData.coinsCost,
    stock: rewardData.stock,
    isActive: rewardData.isActive !== false,
  };

  const isTemporaryId = id && (id.startsWith('reward') || !id.startsWith('cm'));

  if (id && !isTemporaryId) {
    const existing = await prisma.reward.findUnique({ where: { id } });
    if (existing) {
      return prisma.reward.update({
        where: { id },
        data: rewardPayload,
      });
    }
  }

  return prisma.reward.create({ data: rewardPayload });
}

export async function deleteReward(id: string) {
  return prisma.reward.delete({ where: { id } });
}

// ============================================
// MESSAGES (MENSAJES DE CHAT)
// ============================================

export async function getMessages(orderId?: string) {
  const where = orderId ? { orderId } : {};

  const messages = await prisma.message.findMany({
    where,
    orderBy: { createdAt: "asc" },
  });

  return messages.map((m) => ({
    id: m.id,
    orderId: m.orderId,
    senderId: m.senderId,
    senderName: m.senderName,
    senderRole: m.senderRole,
    content: m.content,
    timestamp: m.createdAt.toISOString(),
    isRead: m.isRead,
  }));
}

export async function saveMessage(data: any) {
  const { id, ...messageData } = data;

  const messagePayload = {
    orderId: messageData.orderId,
    senderId: messageData.senderId,
    senderName: messageData.senderName,
    senderRole: messageData.senderRole,
    content: messageData.content,
    isRead: messageData.isRead || false,
  };

  const isTemporaryId = id && (id.startsWith('msg') || !id.startsWith('cm'));

  if (id && !isTemporaryId) {
    const existing = await prisma.message.findUnique({ where: { id } });
    if (existing) {
      return prisma.message.update({
        where: { id },
        data: messagePayload,
      });
    }
  }

  return prisma.message.create({ data: messagePayload });
}

export async function deleteMessage(id: string) {
  return prisma.message.delete({ where: { id } });
}

export async function markMessagesAsRead(orderId: string, recipientRole: string) {
  return prisma.message.updateMany({
    where: {
      orderId,
      senderRole: { not: recipientRole },
      isRead: false,
    },
    data: { isRead: true },
  });
}

// ============================================
// DRIVER DEBT TRANSACTIONS
// ============================================

export async function getDriverDebtTransactions(driverId: string) {
  const transactions = await prisma.driverDebtTransaction.findMany({
    where: { driverId },
    orderBy: { createdAt: "desc" },
  });

  return transactions.map((t) => ({
    id: t.id,
    driverId: t.driverId,
    date: t.createdAt.toISOString(),
    type: t.type,
    amount: decimalToNumber(t.amount),
    description: t.description || "",
  }));
}

export async function saveDriverDebtTransaction(data: any) {
  const { id, ...transactionData } = data;

  const transactionPayload = {
    driverId: transactionData.driverId,
    type: transactionData.type,
    amount: transactionData.amount,
    description: transactionData.description,
  };

  const isTemporaryId = id && (id.startsWith('debt') || !id.startsWith('cm'));

  if (id && !isTemporaryId) {
    const existing = await prisma.driverDebtTransaction.findUnique({ where: { id } });
    if (existing) {
      return prisma.driverDebtTransaction.update({
        where: { id },
        data: transactionPayload,
      });
    }
  }

  return prisma.driverDebtTransaction.create({ data: transactionPayload });
}

export async function deleteDriverDebtTransaction(id: string) {
  return prisma.driverDebtTransaction.delete({ where: { id } });
}
