"use client";

import React, { useState, useEffect } from 'react';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useToast } from '@/hooks/use-toast';
import { setupRecaptcha, sendPhoneVerificationCode, verifyPhoneCode } from '@/lib/firebase';
import { RecaptchaVerifier, ConfirmationResult } from 'firebase/auth';
import { Loader2 } from 'lucide-react';

interface PhoneAuthModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSuccess: (user: { uid: string; phoneNumber: string | null; displayName: string | null }) => void;
}

export function PhoneAuthModal({ isOpen, onClose, onSuccess }: PhoneAuthModalProps) {
  const [phoneNumber, setPhoneNumber] = useState('');
  const [verificationCode, setVerificationCode] = useState('');
  const [confirmationResult, setConfirmationResult] = useState<ConfirmationResult | null>(null);
  const [recaptchaVerifier, setRecaptchaVerifier] = useState<RecaptchaVerifier | null>(null);
  const [isLoading, setIsLoading] = useState(false);
  const [step, setStep] = useState<'phone' | 'code'>('phone');
  const { toast } = useToast();

  // Inicializar reCAPTCHA cuando se abre el modal
  useEffect(() => {
    if (isOpen && !recaptchaVerifier) {
      // Esperar a que el DOM esté listo
      const timer = setTimeout(() => {
        const container = document.getElementById('recaptcha-container');
        if (container) {
          try {
            const verifier = setupRecaptcha('recaptcha-container');
            setRecaptchaVerifier(verifier);
          } catch (error) {
            console.error('Error al configurar reCAPTCHA:', error);
            toast({
              title: 'Error de configuración',
              description: 'No se pudo inicializar el sistema de verificación. Por favor, recarga la página.',
              variant: 'destructive'
            });
          }
        }
      }, 100);

      return () => clearTimeout(timer);
    }

    // Limpiar cuando se cierra el modal
    if (!isOpen && recaptchaVerifier) {
      try {
        recaptchaVerifier.clear();
      } catch (error) {
        console.error('Error al limpiar reCAPTCHA:', error);
      }
      setRecaptchaVerifier(null);
    }
  }, [isOpen, recaptchaVerifier, toast]);

  const handleSendCode = async () => {
    if (!phoneNumber) {
      toast({
        title: 'Campo requerido',
        description: 'Por favor ingresa tu número de teléfono.',
        variant: 'destructive'
      });
      return;
    }

    // Validar formato de número (debe empezar con +)
    if (!phoneNumber.startsWith('+')) {
      toast({
        title: 'Formato inválido',
        description: 'El número debe incluir el código de país (ejemplo: +51999999999)',
        variant: 'destructive'
      });
      return;
    }

    if (!recaptchaVerifier) {
      toast({
        title: 'Error',
        description: 'El sistema de verificación no está listo. Por favor, recarga la página.',
        variant: 'destructive'
      });
      return;
    }

    setIsLoading(true);

    try {
      const result = await sendPhoneVerificationCode(phoneNumber, recaptchaVerifier);

      if (result.success && result.confirmationResult) {
        setConfirmationResult(result.confirmationResult);
        setStep('code');
        toast({
          title: 'Código enviado',
          description: 'Hemos enviado un código de verificación a tu teléfono.',
        });
      } else {
        toast({
          title: 'Error al enviar código',
          description: result.error || 'No se pudo enviar el código de verificación.',
          variant: 'destructive'
        });
      }
    } catch (error) {
      console.error('Error:', error);
      toast({
        title: 'Error',
        description: 'Ocurrió un error inesperado. Por favor, intenta nuevamente.',
        variant: 'destructive'
      });
    } finally {
      setIsLoading(false);
    }
  };

  const handleVerifyCode = async () => {
    if (!verificationCode) {
      toast({
        title: 'Campo requerido',
        description: 'Por favor ingresa el código de verificación.',
        variant: 'destructive'
      });
      return;
    }

    if (!confirmationResult) {
      toast({
        title: 'Error',
        description: 'No hay un proceso de verificación activo.',
        variant: 'destructive'
      });
      return;
    }

    setIsLoading(true);

    try {
      const result = await verifyPhoneCode(confirmationResult, verificationCode);

      if (result.success && result.user) {
        toast({
          title: '¡Bienvenido!',
          description: 'Has iniciado sesión correctamente.',
        });
        onSuccess(result.user);
        handleClose();
      } else {
        toast({
          title: 'Código inválido',
          description: result.error || 'El código ingresado no es correcto.',
          variant: 'destructive'
        });
      }
    } catch (error) {
      console.error('Error:', error);
      toast({
        title: 'Error',
        description: 'No se pudo verificar el código. Por favor, intenta nuevamente.',
        variant: 'destructive'
      });
    } finally {
      setIsLoading(false);
    }
  };

  const handleClose = () => {
    setPhoneNumber('');
    setVerificationCode('');
    setConfirmationResult(null);
    setStep('phone');
    onClose();
  };

  return (
    <Dialog open={isOpen} onOpenChange={handleClose}>
      <DialogContent className="sm:max-w-md">
        <DialogHeader>
          <DialogTitle>
            {step === 'phone' ? 'Iniciar sesión con teléfono' : 'Verificar código'}
          </DialogTitle>
          <DialogDescription>
            {step === 'phone'
              ? 'Ingresa tu número de teléfono para recibir un código de verificación.'
              : 'Ingresa el código de 6 dígitos que enviamos a tu teléfono.'}
          </DialogDescription>
        </DialogHeader>

        <div className="space-y-4 py-4">
          {step === 'phone' ? (
            <div className="space-y-2">
              <Label htmlFor="phone">Número de teléfono</Label>
              <Input
                id="phone"
                type="tel"
                placeholder="+51999999999"
                value={phoneNumber}
                onChange={(e) => setPhoneNumber(e.target.value)}
                disabled={isLoading}
              />
              <p className="text-xs text-muted-foreground">
                Incluye el código de país (ej: +51 para Perú)
              </p>
            </div>
          ) : (
            <div className="space-y-2">
              <Label htmlFor="code">Código de verificación</Label>
              <Input
                id="code"
                type="text"
                placeholder="123456"
                value={verificationCode}
                onChange={(e) => setVerificationCode(e.target.value)}
                maxLength={6}
                disabled={isLoading}
              />
              <Button
                variant="link"
                className="h-auto p-0 text-xs"
                onClick={() => {
                  setStep('phone');
                  setVerificationCode('');
                  setConfirmationResult(null);
                }}
                disabled={isLoading}
              >
                ¿No recibiste el código? Intentar con otro número
              </Button>
            </div>
          )}

          {/* Contenedor invisible para reCAPTCHA */}
          <div id="recaptcha-container"></div>
        </div>

        <div className="flex justify-end gap-2">
          <Button
            variant="outline"
            onClick={handleClose}
            disabled={isLoading}
          >
            Cancelar
          </Button>
          <Button
            onClick={step === 'phone' ? handleSendCode : handleVerifyCode}
            disabled={isLoading}
          >
            {isLoading && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
            {step === 'phone' ? 'Enviar código' : 'Verificar'}
          </Button>
        </div>
      </DialogContent>
    </Dialog>
  );
}
