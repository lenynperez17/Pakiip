// src/app/order/[orderId]/receipt/ReceiptPageContent.tsx
"use client";

import React, { useRef, useState, useEffect } from 'react';
import jsPDF from 'jspdf';
import html2canvas from 'html2canvas';
import { Button } from "@/components/ui/button";
import { Printer, Download } from "lucide-react";
import { useAppData } from "@/hooks/use-app-data";
import { Vendor, OrderItem, Order } from "@/lib/placeholder-data";
import { PakiipCharacter } from '@/components/PakiipCharacter';

// This is a pure Client Component. It receives orderId as a prop.
export default function ReceiptPageContent({ orderId }: { orderId: string }) {
  const { appSettings: settings, orders, vendors } = useAppData();
  const receiptRef = useRef<HTMLDivElement>(null);
  const [showThankYou, setShowThankYou] = useState(true);
  const [order, setOrder] = useState<Order | null>(null);

  useEffect(() => {
    const foundOrder = orders.find(o => o.id === orderId);
    setOrder(foundOrder || null);
  }, [orderId, orders]);


  if (!order) {
    return <div>Pedido no encontrado</div>;
  }

  const groupedItems = order.items.reduce((acc, item) => {
    const vendorName = item.vendor;
    const vendor = vendors.find(v => v.name === vendorName);
    if (!acc[vendorName]) {
      acc[vendorName] = {
        vendor: vendor,
        items: []
      };
    }
    acc[vendorName].items.push(item);
    return acc;
  }, {} as { [vendorName: string]: { vendor: Vendor | undefined; items: OrderItem[] } });

  const subtotal = order.items.reduce((acc, item) => {
      let optionsPrice = 0; // Simplified for display, real total is in `order.total`
      return acc + (item.price * item.quantity);
  }, 0);
  
  const tax = order.total * (settings.taxRate / (100 + settings.taxRate));
  const shipping = order.shippingFee;
  const subtotalBeforeFees = order.total - tax - (shipping || 0);

  const handleDownloadPdf = () => {
    const input = receiptRef.current;
    if (!input) return;

    html2canvas(input, { scale: 2 }).then((canvas) => {
      const imgData = canvas.toDataURL('image/png');
      const pdf = new jsPDF({
        orientation: 'portrait',
        unit: 'px',
        format: [canvas.width, canvas.height]
      });
      pdf.addImage(imgData, 'PNG', 0, 0, canvas.width, canvas.height);
      pdf.save(`recibo-${order.id}.pdf`);
    });
  };

  return (
    <>
      {showThankYou && (
        <PakiipCharacter 
            message="¡Gracias por tu compra!"
            onComplete={() => setShowThankYou(false)}
        />
      )}
      <div className="bg-gray-100 min-h-screen py-12 flex flex-col items-center">
        <div className="w-full max-w-md bg-white p-6 shadow-lg" ref={receiptRef}>
              <style>{`
                  @media print {
                      @page {
                        size: 80mm;
                        margin: 0;
                      }
                      body * {
                          visibility: hidden;
                      }
                      .receipt-container, .receipt-container * {
                          visibility: visible;
                      }
                      .receipt-container {
                          position: absolute;
                          left: 0;
                          top: 0;
                          width: 80mm;
                          margin: 0;
                          padding: 0;
                          box-shadow: none;
                          border: none;
                      }
                      .no-print {
                          display: none;
                      }
                  }
              `}</style>
              <div className="receipt-container">
                  <div className="text-center mb-4">
                      <h1 className="text-xl font-bold uppercase">{settings.appName}</h1>
                      <p className="text-sm">RECIBO DE VENTA</p>
                  </div>

                  <div className="mb-2">
                      <p>PEDIDO #: {order.id}</p>
                      <p>FECHA: {new Date(order.date).toLocaleString('es-ES')}</p>
                      <p>CLIENTE: {order.customerName}</p>
                      <p>MÉTODO PAGO: {order.paymentMethod.toUpperCase()}</p>
                  </div>

                  <hr className="my-4 border-t border-dashed border-gray-400"/>
                  
                  {Object.entries(groupedItems).map(([vendorName, { vendor, items }]) => (
                      <div key={vendorName} className="my-4">
                          <div className="text-center mb-2">
                              <h2 className="font-bold uppercase">{vendor?.name || vendorName}</h2>
                              {vendor?.address && <p className="text-xs text-gray-600">{vendor.address}</p>}
                          </div>
                          <hr className="my-2 border-t border-dashed border-gray-300"/>
                          <table className="w-full text-sm">
                              <thead>
                                  <tr>
                                      <th className="text-left">Cant</th>
                                      <th className="text-left">Artículo</th>
                                      <th className="text-right">Total</th>
                                  </tr>
                              </thead>
                              <tbody>
                                  {items.map((item, index) => {
                                      const hasOptions = item.options && (item.options.cutlery || item.options.drink);
                                      return (
                                      <React.Fragment key={`${vendorName}-${index}`}>
                                          <tr>
                                              <td>{item.quantity}x</td>
                                              <td>{item.productName}</td>
                                              <td className="text-right">{settings.currencySymbol}{(item.price * item.quantity).toFixed(2)}</td>
                                          </tr>
                                          {hasOptions &&
                                              <tr className="text-gray-500">
                                                  <td></td>
                                                  <td colSpan={2} className="text-xs pl-2">
                                                      {item.options.cutlery && <span>+ Cubiertos</span>}
                                                      {item.options.cutlery && item.options.drink && <br/>}
                                                      {item.options.drink && <span>+ Bebida: {item.options.drink}</span>}
                                                  </td>
                                              </tr>
                                          }
                                      </React.Fragment>
                                      )
                                  })}
                              </tbody>
                          </table>
                          <hr className="my-2 border-t border-dashed border-gray-300"/>
                      </div>
                  ))}

                  <div className="space-y-1 mt-4">
                      <div className="flex justify-between">
                          <span>SUBTOTAL:</span>
                          <span>{settings.currencySymbol}{subtotalBeforeFees.toFixed(2)}</span>
                      </div>
                      <div className="flex justify-between">
                          <span>IMPUESTO ({settings.taxRate}%):</span>
                          <span>{settings.currencySymbol}{tax.toFixed(2)}</span>
                      </div>
                      <div className="flex justify-between">
                          <span>ENVÍO:</span>
                          <span>{settings.currencySymbol}{order.shippingFee.toFixed(2)}</span>
                      </div>
                      <hr className="my-2 border-t border-dashed border-gray-400" />
                      <div className="flex justify-between font-bold text-lg mt-1">
                          <span>TOTAL:</span>
                          <span>{settings.currencySymbol}{order.total.toFixed(2)}</span>
                      </div>
                  </div>
                  
                  {order.verificationCode && (
                     <div className="text-center mt-4 border-t border-dashed border-gray-400 pt-2">
                        <p className="font-semibold">CÓDIGO DE ENTREGA:</p>
                        <p className="text-2xl font-bold tracking-widest">{order.verificationCode}</p>
                     </div>
                  )}

                  <div className="text-center mt-6">
                      <p>¡Gracias por tu compra!</p>
                  </div>
              </div>
        </div>
        <div className="mt-8 flex gap-4 no-print">
              <Button onClick={() => window.print()}>
                  <Printer className="mr-2 h-4 w-4" /> Imprimir Recibo
              </Button>
              <Button onClick={handleDownloadPdf} variant="outline">
                  <Download className="mr-2 h-4 w-4" /> Descargar PDF
              </Button>
        </div>
      </div>
    </>
  );
}
