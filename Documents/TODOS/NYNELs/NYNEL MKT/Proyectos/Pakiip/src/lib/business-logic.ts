

import type { Order, Vendor, EnrichedCartItem, AppSettings, DeliveryDriver, City, Coordinate, DeliveryZone } from './placeholder-data';
import { getDistanceFromLatLonInKm } from './utils';


/**
 * Calculates metrics for a specific vendor based on all orders.
 */
export function calculateVendorMetrics(vendor: Vendor | undefined, allOrders: Order[]) {
    if (!vendor) {
        return {
            vendorOrders: [],
            totalRevenue: 0,
            totalOrders: 0,
            uniqueCustomers: 0,
            averageOrderValue: 0,
            totalCost: 0,
            grossProfit: 0,
            profitMargin: 0,
            platformCommission: 0,
            netPayout: 0,
        };
    }

    const vendorOrders = allOrders.filter(order => order.items.some(item => item.vendor === vendor.name));

    let totalRevenue = 0;
    let totalCost = 0;
    const customerSet = new Set<string>();

    vendorOrders.forEach(order => {
        const itemsFromVendor = order.items.filter(item => item.vendor === vendor.name);
        const revenueFromOrder = itemsFromVendor.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const costFromOrder = itemsFromVendor.reduce((sum, item) => sum + (item.costPrice * item.quantity), 0);
        
        totalRevenue += revenueFromOrder;
        totalCost += costFromOrder;
        customerSet.add(order.customerName);
    });
    
    const totalOrders = vendorOrders.length;
    const platformCommission = totalRevenue * (vendor.commissionRate / 100);
    const grossProfit = totalRevenue - totalCost;
    const profitMargin = totalRevenue > 0 ? (grossProfit / totalRevenue) * 100 : 0;
    const averageOrderValue = totalOrders > 0 ? totalRevenue / totalOrders : 0;
    
    const pendingPayoutOrders = allOrders.filter(o => o.items.some(item => item.vendor === vendor.name && item.payoutStatus === 'pending'));
    const netPayout = pendingPayoutOrders.reduce((sum, order) => {
        const revenueFromOrder = order.items
            .filter(item => item.vendor === vendor.name)
            .reduce((itemSum, item) => itemSum + (item.price * item.quantity), 0);
        return sum + (revenueFromOrder * (1 - (vendor.commissionRate / 100)));
    }, 0);


    return {
        vendorOrders,
        totalRevenue,
        totalOrders,
        uniqueCustomers: customerSet.size,
        averageOrderValue,
        totalCost,
        grossProfit,
        profitMargin,
        platformCommission,
        netPayout,
    };
}


/**
 * Calculates the totals for a shopping cart.
 */
export function calculateCartTotals(
    cartItems: EnrichedCartItem[],
    vendors: Vendor[], 
    deliveryZones: DeliveryZone[],
    selectedCity: City | undefined,
    settings: AppSettings,
    customerLocation?: Coordinate
) {
    const subtotal = cartItems.reduce((acc, item) => {
        const price = item.product.offerPrice ?? item.product.price;
        let optionsPrice = 0;
        if (item.options?.cutlery && item.product.options?.cutleryPrice) {
            optionsPrice += item.product.options.cutleryPrice;
        }
        if (item.options?.drink) {
            const drinkOption = item.product.options?.drinks?.find(d => d.name === item.options.drink);
            if (drinkOption) {
                optionsPrice += drinkOption.price;
            }
        }
        return acc + (price + optionsPrice) * item.quantity;
    }, 0);

    const itemsByVendor = cartItems.reduce((acc, item) => {
        const vendorId = item.product.vendorId;
        if (!acc[vendorId]) {
            acc[vendorId] = [];
        }
        acc[vendorId].push(item);
        return acc;
    }, {} as Record<string, EnrichedCartItem[]>);
    
    const totalCommission = Object.keys(itemsByVendor).reduce((acc, vendorId) => {
        const vendor = vendors.find(v => v.id === vendorId);
        const vendorItems = itemsByVendor[vendorId];
        const vendorRevenueInCart = vendorItems.reduce((sum, item) => {
            const price = item.product.offerPrice ?? item.product.price;
            return sum + (price * item.quantity);
        }, 0);

        if (vendor) {
            return acc + (vendorRevenueInCart * (vendor.commissionRate / 100));
        }
        return acc;
    }, 0);


    const additionalFees = cartItems.reduce((acc, item) => {
        return acc + ((item.product.options?.packagingFee || 0) * item.quantity);
    }, 0);

    let shipping = 0;
    
    if (customerLocation && selectedCity) {
        // Find if the customer location is inside a defined delivery zone for the selected city
        const cityZones = deliveryZones.filter(z => z.cityId === selectedCity.id);
        let zoneFound = false;

        for (const zone of cityZones) {
            if (isPointInPolygon(customerLocation, zone.path)) {
                shipping = zone.shippingFee;
                zoneFound = true;
                break;
            }
        }

        // If not in a defined zone, fall back to distance-based calculation
        if (!zoneFound) {
            const vendorIdsInCart = Object.keys(itemsByVendor);
            const vendorsInCart = vendors.filter(v => vendorIdsInCart.includes(v.id));

            let maxDistance = 0;
            for (const vendor of vendorsInCart) {
                if (vendor.coordinates) {
                    const distance = getDistanceFromLatLonInKm(
                        vendor.coordinates.lat, 
                        vendor.coordinates.lng, 
                        customerLocation.lat, 
                        customerLocation.lng
                    );
                    if (distance > maxDistance) {
                        maxDistance = distance;
                    }
                }
            }
            
            if (maxDistance <= settings.shipping.baseRadiusKm) {
                shipping = settings.shipping.baseFee;
            } else {
                const extraDistance = maxDistance - settings.shipping.baseRadiusKm;
                shipping = settings.shipping.baseFee + (extraDistance * settings.shipping.feePerKm);
            }
        }
    }
    
    let tax = 0;
    if (settings.taxType === 'gravada') {
        tax = totalCommission * (settings.taxRate / 100);
    }
    
    const total = subtotal + additionalFees + tax + shipping;

    return {
        subtotal,
        tax,
        shipping,
        total,
        additionalFees,
    };
}


/**
 * Calculates financial metrics for the entire platform.
 */
export function calculatePlatformMetrics(
    allOrders: Order[], 
    allVendors: Vendor[], 
    allDrivers: DeliveryDriver[],
    settings: AppSettings
) {
    const completedOrders = allOrders.filter(o => o.status === 'Entregado');
    let platformCommissions = 0;
    let totalDriverPayouts = 0;
    let totalShippingFees = 0;

    for (const order of completedOrders) {
        // Group items by vendor for this order
        const itemsByVendor = order.items.reduce((acc, item) => {
            if (!acc[item.vendor]) {
                acc[item.vendor] = [];
            }
            acc[item.vendor].push(item);
            return acc;
        }, {} as Record<string, typeof order.items>);

        for (const vendorName in itemsByVendor) {
            const vendor = allVendors.find(v => v.name === vendorName);
            if (!vendor) continue;

            const vendorItems = itemsByVendor[vendorName];
            const vendorRevenue = vendorItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            
            platformCommissions += vendorRevenue * (vendor.commissionRate / 100);
        }

        // Calculate driver payouts
        if (order.driverId) {
            const driver = allDrivers.find(d => d.id === order.driverId);
            if (driver) {
                totalDriverPayouts += order.shippingFee * (driver.commissionRate / 100);
            }
        }

        totalShippingFees += order.shippingFee;
    }

    const totalRevenue = allOrders.reduce((sum, order) => sum + order.total, 0);
    const totalOrders = allOrders.length;
    const uniqueCustomers = new Set(allOrders.map(o => o.customerName)).size;
    const averageOrderValue = totalOrders > 0 ? totalRevenue / totalOrders : 0;
    const netDeliveryRevenue = totalShippingFees - totalDriverPayouts;
    const netPlatformProfit = platformCommissions + netDeliveryRevenue;

    return {
        totalRevenue,
        totalOrders,
        uniqueCustomers,
        averageOrderValue,
        platformCommissions,
        netDeliveryRevenue,
        totalDriverPayouts,
        netPlatformProfit,
    };
}

/**
 * Calculates the net payout for each vendor after platform commissions.
 */
export function calculateVendorPayouts(allOrders: Order[], allVendors: Vendor[]) {
    const payouts = allVendors.map(vendor => {
        const pendingPayoutOrders = allOrders.filter(o => 
            o.items.some(item => item.vendor === vendor.name && item.payoutStatus === 'pending')
        );
        const netPayout = pendingPayoutOrders.reduce((sum, order) => {
            const revenueFromOrder = order.items
                .filter(item => item.vendor === vendor.name)
                .reduce((itemSum, item) => itemSum + (item.price * item.quantity), 0);
            return sum + (revenueFromOrder * (1 - (vendor.commissionRate / 100)));
        }, 0);

        return {
            vendorId: vendor.id,
            vendorName: vendor.name,
            totalOrders: pendingPayoutOrders.length,
            netPayout: netPayout,
        };
    });

    return payouts;
}

/**
 * Calculates the net payout for each driver.
 */
export function calculateDriverPayouts(allOrders: Order[], allDrivers: DeliveryDriver[]) {
    const payouts = allDrivers.map(driver => {
        const deliveredOrdersForDriver = allOrders.filter(o => 
            o.driverId === driver.id && o.status === 'Entregado' && o.driverPayoutStatus !== 'paid'
        );
        
        const totalCommissions = deliveredOrdersForDriver.reduce((sum, order) => {
            return sum + (order.shippingFee * (driver.commissionRate / 100));
        }, 0);
        
        const netBalance = totalCommissions - driver.debt;

        return {
            driverId: driver.id,
            driverName: driver.name,
            totalCommissions,
            currentDebt: driver.debt,
            netBalance,
        };
    });
    
    return payouts;
}

/**
 * Checks if a geographical point is inside a polygon.
 * @param point The point to check ({ lat, lng }).
 * @param polygon An array of points that form the polygon's path.
 * @returns True if the point is inside the polygon, false otherwise.
 */
function isPointInPolygon(point: Coordinate, polygon: Coordinate[]): boolean {
    const { lat, lng } = point;
    let isInside = false;

    for (let i = 0, j = polygon.length - 1; i < polygon.length; j = i++) {
        const xi = polygon[i].lat, yi = polygon[i].lng;
        const xj = polygon[j].lat, yj = polygon[j].lng;

        const intersect = ((yi > lng) !== (yj > lng))
            && (lat < (xj - xi) * (lng - yi) / (yj - yi) + xi);
            
        if (intersect) {
            isInside = !isInside;
        }
    }
    return isInside;
}
