

"use client";

import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { getProductRecommendations, ProductRecommendationInput, ProductRecommendationOutput } from '@/ai/flows/product-recommendation';
import { useCart } from '@/hooks/use-cart';
import { useAppData } from '@/hooks/use-app-data';
import { Product } from '@/lib/placeholder-data';
import { Loader2, Sparkles, Wand2 } from 'lucide-react';
import { ProductCard } from '@/components/ProductCard';

export default function RecommendationsPage() {
    const [recommendations, setRecommendations] = useState<ProductRecommendationOutput | null>(null);
    const [recommendedProducts, setRecommendedProducts] = useState<Product[]>([]);
    const [isLoading, setIsLoading] = useState(false);
    const { items: cartItems } = useCart();
    const { getAllProducts, getProductById, orders } = useAppData();

    const handleGetRecommendations = async () => {
        setIsLoading(true);
        setRecommendations(null);
        setRecommendedProducts([]);

        const currentCart = JSON.stringify(cartItems.map(item => ({ name: item.product.name, quantity: item.quantity })));
        const orderHistory = JSON.stringify(orders.slice(0, 5)); // Use recent orders for history
        
        // Provide the AI with a list of available products to choose from
        const availableProducts = getAllProducts().map(({ id, name, description }) => ({ id, name, description }));

        const input: ProductRecommendationInput = {
            availableProducts: JSON.stringify(availableProducts),
            orderHistory,
            currentCart,
        };

        try {
            const result = await getProductRecommendations(input);
            setRecommendations(result);
            
            // The AI returns product IDs, so we need to get the full product objects
            if (result.recommendedProductIds) {
                const productIds = JSON.parse(result.recommendedProductIds);
                const products = productIds.map((id: string) => getProductById(id)).filter(Boolean);
                setRecommendedProducts(products as Product[]);
            }

        } catch (error) {
            console.error("Error getting recommendations:", error);
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <div className="px-4 sm:px-6 md:px-8 py-8 sm:py-12 md:py-16">
            <div className="max-w-6xl mx-auto space-y-8 sm:space-y-10 md:space-y-12">
                <section className="text-center space-y-3 sm:space-y-4">
                    <h1 className="text-3xl sm:text-4xl md:text-5xl font-bold font-headline tracking-tight flex items-center justify-center gap-2 sm:gap-3">
                        <Wand2 className="h-8 w-8 sm:h-10 sm:w-10 md:h-12 md:w-12 text-primary" />
                        Recomendaciones para Ti
                    </h1>
                    <p className="text-sm sm:text-base md:text-lg text-muted-foreground max-w-2xl mx-auto px-2">
                        Usa nuestra IA para descubrir productos que podrían encantarte, basándonos en tus gustos y compras anteriores.
                    </p>
                </section>

                <div className="flex justify-center">
                    <Button
                        size="lg"
                        onClick={handleGetRecommendations}
                        disabled={isLoading}
                        className="text-sm sm:text-base md:text-lg px-4 sm:px-6 md:px-8 py-2 sm:py-3"
                    >
                        {isLoading ? (
                            <>
                                <Loader2 className="mr-2 h-4 w-4 sm:h-5 sm:w-5 animate-spin" />
                                <span className="hidden sm:inline">Generando...</span>
                                <span className="sm:hidden">Generando...</span>
                            </>
                        ) : (
                            <>
                                <Sparkles className="mr-2 h-4 w-4 sm:h-5 sm:w-5" />
                                <span className="hidden sm:inline">Obtener Mis Recomendaciones</span>
                                <span className="sm:hidden">Recomendaciones</span>
                            </>
                        )}
                    </Button>
                </div>

                {recommendations && (
                    <div className="space-y-8 sm:space-y-10 md:space-y-12">
                        <Card className="bg-muted/50">
                            <CardHeader className="p-4 sm:p-6">
                                <CardTitle className="text-lg sm:text-xl md:text-2xl">El Porqué de las Sugerencias</CardTitle>
                            </CardHeader>
                            <CardContent className="p-4 sm:p-6">
                                <p className="text-sm sm:text-base md:text-lg text-muted-foreground leading-relaxed">{recommendations.reasoning}</p>
                            </CardContent>
                        </Card>

                        <div className="space-y-4 sm:space-y-6">
                            <h2 className="text-xl sm:text-2xl md:text-3xl font-bold font-headline">Productos Recomendados</h2>
                            <div className="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-6">
                                {recommendedProducts.map((product) => (
                                    <ProductCard key={product.id} product={product} />
                                ))}
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
}
