"use client";

import React from 'react';
import { AddRoleCard } from '@/components/AddRoleCard';
import { ArrowLeft } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { useRouter } from 'next/navigation';

export default function AddRolePage() {
  const router = useRouter();

  return (
    <div className="min-h-screen bg-gradient-to-b from-primary/10 to-background p-4">
      <div className="max-w-4xl mx-auto py-8">
        <Button
          variant="ghost"
          className="mb-4"
          onClick={() => router.back()}
        >
          <ArrowLeft className="w-4 h-4 mr-2" />
          Volver
        </Button>

        <div className="text-center mb-8">
          <h1 className="text-3xl font-bold mb-2">Expande tus oportunidades</h1>
          <p className="text-muted-foreground">
            Registra nuevos roles y accede a más funcionalidades en Pakiip
          </p>
        </div>

        <AddRoleCard />
      </div>
    </div>
  );
}
