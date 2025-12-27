
"use client";

import { useState } from "react";
import { Button } from "@/components/ui/button";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { MoreHorizontal, PlusCircle, Scale, DollarSign, ArrowDownCircle, ArrowUpCircle } from "lucide-react";
import { DeliveryDriver, DebtTransaction } from "@/lib/placeholder-data";
import { useAppData } from "@/hooks/use-app-data";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { useToast } from "@/hooks/use-toast";
import { formatCurrency } from "@/lib/utils";
import { Badge } from "@/components/ui/badge";
import { cn } from "@/lib/utils";
import { AuthGuard } from "@/components/AuthGuard";

function AdminDriverDebtsPageContent() {
  const { drivers, appSettings, clearDriverDebt, addCreditTransaction } = useAppData();
  const { toast } = useToast();

  const [isCreditDialogOpen, setCreditDialogOpen] = useState(false);
  const [selectedDriver, setSelectedDriver] = useState<DeliveryDriver | null>(null);
  const [creditAmount, setCreditAmount] = useState<number | ''>('');
  const [creditDescription, setCreditDescription] = useState('');

  const handleRegisterPaymentClick = (driver: DeliveryDriver) => {
    setSelectedDriver(driver);
    setCreditAmount('');
    setCreditDescription('');
    setCreditDialogOpen(true);
  };

  const handleRegisterPayment = () => {
    if (!selectedDriver || !creditAmount || +creditAmount <= 0) {
      toast({
        title: "Error",
        description: "Por favor, introduce un monto válido.",
        variant: "destructive",
      });
      return;
    }
    
    addCreditTransaction(selectedDriver.id, +creditAmount, creditDescription || 'Pago/depósito registrado por admin');
    
    toast({
      title: "Pago Registrado",
      description: `Se ha registrado un pago de ${formatCurrency(+creditAmount, appSettings.currencySymbol)} para ${selectedDriver.name}.`,
    });
    
    setCreditDialogOpen(false);
  };
  
  const getDebtTransactions = (driver: DeliveryDriver) => {
    return driver.debtTransactions || [];
  };

  return (
    <div className="space-y-4 sm:space-y-6">
      <Card>
        <CardHeader className="px-4 sm:px-6">
          <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
              <CardTitle className="text-xl sm:text-2xl">Deudas de Repartidores</CardTitle>
              <CardDescription className="text-sm sm:text-base">
                Gestiona las deudas por cobros en efectivo y registra los pagos.
              </CardDescription>
            </div>
          </div>
        </CardHeader>
        <CardContent className="px-4 sm:px-6">
            {/* Mobile View */}
            <div className="grid gap-3 sm:gap-4 md:hidden">
              {drivers.map((driver) => (
                <Card key={driver.id} className="p-3 sm:p-4">
                  <div className="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3">
                      <div className="flex-1">
                        <p className="font-bold text-base sm:text-lg">{driver.name}</p>
                        <div className="mt-2">
                          <span className="text-sm text-muted-foreground">Deuda Actual: </span>
                          <span className={cn("font-bold text-base sm:text-lg", driver.debt > 0 ? "text-red-600" : "text-green-600")}>
                            {formatCurrency(driver.debt, appSettings.currencySymbol)}
                          </span>
                        </div>
                      </div>
                      <Button
                        size="sm"
                        onClick={() => handleRegisterPaymentClick(driver)}
                        disabled={driver.debt <= 0}
                        className="w-full sm:w-auto h-9 sm:h-10 whitespace-nowrap text-sm"
                      >
                        <PlusCircle className="mr-2 h-4 w-4" /> Registrar Pago
                      </Button>
                  </div>
                </Card>
              ))}
            </div>

            {/* Desktop View */}
            <div className="hidden md:block overflow-x-auto">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Nombre del Repartidor</TableHead>
                    <TableHead className="text-right">Deuda Actual</TableHead>
                    <TableHead className="text-center">Acciones</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {drivers.map((driver) => (
                    <TableRow key={driver.id}>
                      <TableCell className="font-medium whitespace-nowrap">{driver.name}</TableCell>
                      <TableCell className={cn("text-right font-bold whitespace-nowrap", driver.debt > 0 ? "text-red-600" : "text-green-600")}>
                        {formatCurrency(driver.debt, appSettings.currencySymbol)}
                      </TableCell>
                      <TableCell className="text-center">
                         <Button
                          size="sm"
                          onClick={() => handleRegisterPaymentClick(driver)}
                          disabled={driver.debt <= 0}
                          className="whitespace-nowrap"
                        >
                          <PlusCircle className="mr-2 h-4 w-4" /> Registrar Pago
                        </Button>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </div>
        </CardContent>
      </Card>

      <Dialog open={isCreditDialogOpen} onOpenChange={setCreditDialogOpen}>
        <DialogContent className="mx-2 sm:mx-0 sm:max-w-md">
          <DialogHeader className="px-2 sm:px-0">
            <DialogTitle className="text-lg sm:text-xl">Registrar Pago de {selectedDriver?.name}</DialogTitle>
            <DialogDescription className="text-sm sm:text-base">
              Introduce el monto que el repartidor ha pagado/depositado para reducir su deuda.
              Deuda actual: {formatCurrency(selectedDriver?.debt || 0, appSettings.currencySymbol)}
            </DialogDescription>
          </DialogHeader>
          <div className="grid gap-4 py-4 max-h-[70vh] overflow-y-auto px-2 sm:px-0">
            <div className="space-y-2">
              <Label htmlFor="credit-amount" className="text-sm sm:text-base">Monto a Pagar</Label>
               <div className="relative">
                  <span className="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground">{appSettings.currencySymbol}</span>
                  <Input
                    id="credit-amount"
                    type="number"
                    value={creditAmount}
                    onChange={(e) => setCreditAmount(e.target.value === '' ? '' : parseFloat(e.target.value))}
                    className="pl-8 h-9 sm:h-10 text-sm sm:text-base"
                    placeholder="0.00"
                    max={selectedDriver?.debt}
                  />
              </div>
            </div>
             <div className="space-y-2">
                <Label htmlFor="credit-description" className="text-sm sm:text-base">Descripción (Opcional)</Label>
                <Input
                    id="credit-description"
                    value={creditDescription}
                    onChange={(e) => setCreditDescription(e.target.value)}
                    placeholder="Ej: Depósito BCP #12345"
                    className="h-9 sm:h-10 text-sm sm:text-base"
                />
             </div>
          </div>
          <DialogFooter className="px-2 sm:px-0 gap-2 sm:gap-0">
            <Button type="button" variant="ghost" onClick={() => setCreditDialogOpen(false)} className="h-9 sm:h-10 w-full sm:w-auto text-sm sm:text-base">Cancelar</Button>
            <Button onClick={handleRegisterPayment} className="h-9 sm:h-10 w-full sm:w-auto text-sm sm:text-base">Guardar Pago</Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  );
}

export default function AdminDriverDebtsPage() {
  return (
    <AuthGuard requireAuth={true} requireRole="admin" redirectTo="/admin/login">
      <AdminDriverDebtsPageContent />
    </AuthGuard>
  );
}
