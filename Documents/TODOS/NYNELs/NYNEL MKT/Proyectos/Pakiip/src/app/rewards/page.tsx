"use client";

import { useState, useEffect } from "react";
import { useRouter } from "next/navigation";
import { useAppData } from "@/hooks/use-app-data";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Coins, Gift, TrendingUp, ShoppingBag, Truck, Percent, History, ArrowRight, Sparkles } from "lucide-react";
import { useToast } from "@/hooks/use-toast";
import { saveUserCoinsToFirestore, getUserCoinsFromFirestore, addCoinTransactionToFirestore, getRewardsFromFirestore } from "@/lib/firebase/firestore-coins";
import type { CoinTransaction, Reward } from "@/lib/placeholder-data";
import { Skeleton } from "@/components/ui/skeleton";
import { AuthGuard } from "@/components/AuthGuard";

function RewardsPageContent() {
  const router = useRouter();
  const { currentUser } = useAppData();
  const { toast } = useToast();

  const [userCoins, setUserCoins] = useState<number>(0);
  const [transactions, setTransactions] = useState<CoinTransaction[]>([]);
  const [rewards, setRewards] = useState<Reward[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  // Redirigir si no hay usuario autenticado
  useEffect(() => {
    if (!currentUser) {
      router.push('/login');
    }
  }, [currentUser, router]);

  // Cargar datos del usuario desde Firestore
  useEffect(() => {
    if (currentUser) {
      loadUserData();
      loadRewards();
    }
  }, [currentUser]);

  const loadUserData = async () => {
    if (!currentUser) return;

    setIsLoading(true);
    try {
      const userData = await getUserCoinsFromFirestore(currentUser.id);
      if (userData) {
        setUserCoins(userData.coins || 0);
        setTransactions(userData.transactions || []);
      }
    } catch (error) {
      console.error("Error cargando datos del usuario:", error);
      toast({
        title: "Error",
        description: "No se pudieron cargar tus monedas",
        variant: "destructive"
      });
    } finally {
      setIsLoading(false);
    }
  };

  const loadRewards = async () => {
    try {
      const rewardsData = await getRewardsFromFirestore();
      setRewards(rewardsData);
    } catch (error) {
      console.error("Error cargando recompensas:", error);
    }
  };

  const redeemReward = async (reward: Reward) => {
    if (!currentUser) return;

    if (userCoins < reward.coinsCost) {
      toast({
        title: "Monedas insuficientes",
        description: `Necesitas ${reward.coinsCost} monedas para canjear esta recompensa`,
        variant: "destructive"
      });
      return;
    }

    if (reward.stock <= 0) {
      toast({
        title: "Sin stock",
        description: "Esta recompensa no está disponible en este momento",
        variant: "destructive"
      });
      return;
    }

    try {
      const newBalance = userCoins - reward.coinsCost;

      // Crear transacción
      const transaction: Omit<CoinTransaction, 'id'> = {
        userId: currentUser.id,
        type: 'spent',
        amount: -reward.coinsCost,
        reason: 'reward_redeemed',
        description: `Canjeaste: ${reward.name}`,
        timestamp: new Date().toISOString()
      };

      // Guardar en Firestore
      await addCoinTransactionToFirestore(currentUser.id, transaction);
      await saveUserCoinsToFirestore(currentUser.id, newBalance);

      // Actualizar estado local
      setUserCoins(newBalance);

      toast({
        title: "¡Recompensa canjeada!",
        description: `Has canjeado ${reward.name} por ${reward.coinsCost} monedas`
      });

      // Recargar datos
      loadUserData();
    } catch (error) {
      console.error("Error canjeando recompensa:", error);
      toast({
        title: "Error",
        description: "No se pudo canjear la recompensa",
        variant: "destructive"
      });
    }
  };

  const getRewardIcon = (type: Reward['type']) => {
    switch (type) {
      case 'discount':
        return <Percent className="h-5 w-5" />;
      case 'freeDelivery':
        return <Truck className="h-5 w-5" />;
      case 'product':
        return <ShoppingBag className="h-5 w-5" />;
      case 'voucher':
        return <Gift className="h-5 w-5" />;
      default:
        return <Gift className="h-5 w-5" />;
    }
  };

  const getTransactionIcon = (type: CoinTransaction['type']) => {
    switch (type) {
      case 'earned':
        return <TrendingUp className="h-4 w-4 text-green-500" />;
      case 'spent':
        return <ShoppingBag className="h-4 w-4 text-red-500" />;
      case 'bonus':
        return <Sparkles className="h-4 w-4 text-amber-500" />;
      default:
        return <History className="h-4 w-4" />;
    }
  };

  if (!currentUser) {
    return null;
  }

  if (isLoading) {
    return (
      <div className="container mx-auto px-4 py-8 max-w-6xl">
        <Skeleton className="h-12 w-64 mb-8" />
        <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
          {[1, 2, 3].map((i) => (
            <Skeleton key={i} className="h-64" />
          ))}
        </div>
      </div>
    );
  }

  return (
    <div className="px-4 sm:px-6 md:px-8 py-8 sm:py-12 md:py-16">
      <div className="max-w-6xl mx-auto space-y-6 sm:space-y-8">
        {/* Header con balance de monedas */}
        <div className="space-y-3 sm:space-y-4">
          <div>
            <h1 className="text-2xl sm:text-3xl md:text-4xl font-bold font-headline">Monedas Pakiip</h1>
            <p className="text-sm sm:text-base md:text-lg text-muted-foreground mt-1 sm:mt-2">Gana monedas y canjéalas por increíbles recompensas</p>
          </div>
        </div>

        {/* Card de balance */}
        <Card className="bg-gradient-to-r from-amber-500 to-orange-500 text-white border-none">
          <CardHeader className="p-4 sm:p-6">
            <CardTitle className="text-white flex items-center gap-2 text-lg sm:text-xl">
              <Coins className="h-5 w-5 sm:h-6 sm:w-6" />
              Tu balance
            </CardTitle>
          </CardHeader>
          <CardContent className="p-4 sm:p-6 pt-0">
            <div className="text-4xl sm:text-5xl md:text-6xl font-bold font-headline">{userCoins}</div>
            <p className="text-white/80 text-sm sm:text-base mt-2">Monedas Pakiip disponibles</p>
          </CardContent>
        </Card>

        {/* Cómo ganar monedas */}
        <Card>
          <CardHeader className="p-4 sm:p-6">
            <CardTitle className="flex items-center gap-2 text-lg sm:text-xl">
              <TrendingUp className="h-4 w-4 sm:h-5 sm:w-5" />
              ¿Cómo ganar monedas?
            </CardTitle>
          </CardHeader>
          <CardContent className="p-4 sm:p-6 pt-0 grid gap-3 sm:gap-4">
            <div className="flex items-start gap-3">
              <div className="bg-primary/10 p-2 rounded-lg flex-shrink-0">
                <ShoppingBag className="h-4 w-4 sm:h-5 sm:w-5 text-primary" />
              </div>
              <div className="min-w-0">
                <p className="font-semibold text-sm sm:text-base">Realiza pedidos</p>
                <p className="text-xs sm:text-sm text-muted-foreground">Gana 10 monedas por cada S/ 10 en compras</p>
              </div>
            </div>
            <div className="flex items-start gap-3">
              <div className="bg-primary/10 p-2 rounded-lg flex-shrink-0">
                <Sparkles className="h-4 w-4 sm:h-5 sm:w-5 text-primary" />
              </div>
              <div className="min-w-0">
                <p className="font-semibold text-sm sm:text-base">Bonos especiales</p>
                <p className="text-xs sm:text-sm text-muted-foreground">Participa en promociones y gana monedas extra</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Tabs de Recompensas e Historial */}
      <Tabs defaultValue="rewards" className="w-full">
        <TabsList className="grid w-full grid-cols-2">
          <TabsTrigger value="rewards" className="text-xs sm:text-sm">
            <Gift className="h-3 w-3 sm:h-4 sm:w-4 mr-1 sm:mr-2" />
            <span className="hidden sm:inline">Recompensas</span>
            <span className="sm:hidden">Premios</span>
          </TabsTrigger>
          <TabsTrigger value="history" className="text-xs sm:text-sm">
            <History className="h-3 w-3 sm:h-4 sm:w-4 mr-1 sm:mr-2" />
            Historial
          </TabsTrigger>
        </TabsList>

        <TabsContent value="rewards" className="mt-4 sm:mt-6">
          <div className="grid gap-4 sm:gap-6 md:grid-cols-2 lg:grid-cols-3">
            {rewards.length === 0 ? (
              <div className="col-span-full text-center py-8 sm:py-12">
                <Gift className="h-12 w-12 sm:h-16 sm:w-16 mx-auto text-muted-foreground mb-3 sm:mb-4" />
                <p className="text-base sm:text-lg text-muted-foreground px-2">No hay recompensas disponibles en este momento</p>
              </div>
            ) : (
              rewards.filter(r => r.isActive).map((reward) => (
                <Card key={reward.id} className="flex flex-col">
                  <CardHeader className="p-4 sm:p-6">
                    <div className="w-full h-32 sm:h-40 bg-gradient-to-br from-primary/10 to-primary/5 rounded-lg flex items-center justify-center mb-3 sm:mb-4">
                      {getRewardIcon(reward.type)}
                    </div>
                    <CardTitle className="text-base sm:text-lg">{reward.name}</CardTitle>
                    <CardDescription className="text-xs sm:text-sm">{reward.description}</CardDescription>
                  </CardHeader>
                  <CardContent className="flex-grow p-4 sm:p-6 pt-0">
                    <div className="flex items-center justify-between flex-wrap gap-2">
                      <div className="flex items-center gap-2">
                        <Coins className="h-4 w-4 sm:h-5 sm:w-5 text-amber-500" />
                        <span className="text-xl sm:text-2xl font-bold">{reward.coinsCost}</span>
                      </div>
                      <Badge variant={reward.stock > 0 ? "default" : "secondary"} className="text-xs">
                        {reward.stock > 0 ? `${reward.stock} disponibles` : 'Agotado'}
                      </Badge>
                    </div>
                    {reward.type === 'discount' && (
                      <p className="text-xs sm:text-sm text-muted-foreground mt-2">
                        {reward.value}% de descuento
                      </p>
                    )}
                    {reward.type === 'voucher' && (
                      <p className="text-xs sm:text-sm text-muted-foreground mt-2">
                        Vale de S/ {reward.value}
                      </p>
                    )}
                  </CardContent>
                  <CardFooter className="p-4 sm:p-6 pt-0">
                    <Button
                      className="w-full text-sm sm:text-base"
                      onClick={() => redeemReward(reward)}
                      disabled={userCoins < reward.coinsCost || reward.stock <= 0}
                    >
                      Canjear
                      <ArrowRight className="ml-2 h-3 w-3 sm:h-4 sm:w-4" />
                    </Button>
                  </CardFooter>
                </Card>
              ))
            )}
          </div>
        </TabsContent>

        <TabsContent value="history" className="mt-4 sm:mt-6">
          <Card>
            <CardHeader className="p-4 sm:p-6">
              <CardTitle className="text-lg sm:text-xl">Historial de transacciones</CardTitle>
              <CardDescription className="text-xs sm:text-sm">Todas tus monedas ganadas y gastadas</CardDescription>
            </CardHeader>
            <CardContent className="p-4 sm:p-6 pt-0">
              {transactions.length === 0 ? (
                <div className="text-center py-8 sm:py-12">
                  <History className="h-12 w-12 sm:h-16 sm:w-16 mx-auto text-muted-foreground mb-3 sm:mb-4" />
                  <p className="text-base sm:text-lg text-muted-foreground px-2">No tienes transacciones aún</p>
                  <p className="text-xs sm:text-sm text-muted-foreground mt-2 px-2">¡Realiza tu primer pedido para ganar monedas!</p>
                </div>
              ) : (
                <div className="space-y-3 sm:space-y-4">
                  {transactions.sort((a, b) => new Date(b.timestamp).getTime() - new Date(a.timestamp).getTime()).map((transaction) => (
                    <div key={transaction.id} className="flex items-center justify-between p-3 sm:p-4 border rounded-lg gap-3">
                      <div className="flex items-center gap-2 sm:gap-3 min-w-0 flex-1">
                        {getTransactionIcon(transaction.type)}
                        <div className="min-w-0 flex-1">
                          <p className="font-semibold text-sm sm:text-base truncate">{transaction.description}</p>
                          <p className="text-xs sm:text-sm text-muted-foreground">
                            {new Date(transaction.timestamp).toLocaleDateString('es-PE', {
                              year: 'numeric',
                              month: 'short',
                              day: 'numeric',
                              hour: '2-digit',
                              minute: '2-digit'
                            })}
                          </p>
                        </div>
                      </div>
                      <div className={`text-base sm:text-lg font-bold flex-shrink-0 ${transaction.amount > 0 ? 'text-green-500' : 'text-red-500'}`}>
                        {transaction.amount > 0 ? '+' : ''}{transaction.amount}
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  );
}

export default function RewardsPage() {
  return (
    <AuthGuard requireAuth={true} requireRole="customer" redirectTo="/login">
      <RewardsPageContent />
    </AuthGuard>
  );
}
