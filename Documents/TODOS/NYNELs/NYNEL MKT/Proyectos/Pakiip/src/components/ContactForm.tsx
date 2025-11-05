'use client';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

export function ContactForm() {
  const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    // Aquí se puede agregar la lógica para enviar el formulario
    console.log('Formulario enviado');
  };

  return (
    <form className="space-y-4" onSubmit={handleSubmit}>
      <div className="space-y-2">
        <Label htmlFor="name">Nombre completo</Label>
        <Input
          id="name"
          placeholder="Tu nombre"
          required
        />
      </div>

      <div className="space-y-2">
        <Label htmlFor="email">Email</Label>
        <Input
          id="email"
          type="email"
          placeholder="tu@email.com"
          required
        />
      </div>

      <div className="space-y-2">
        <Label htmlFor="phone">Teléfono (opcional)</Label>
        <Input
          id="phone"
          type="tel"
          placeholder="+51 999 999 999"
        />
      </div>

      <div className="space-y-2">
        <Label htmlFor="subject">Asunto</Label>
        <Input
          id="subject"
          placeholder="¿En qué podemos ayudarte?"
          required
        />
      </div>

      <div className="space-y-2">
        <Label htmlFor="message">Mensaje</Label>
        <Textarea
          id="message"
          placeholder="Escribe tu mensaje aquí..."
          rows={6}
          required
        />
      </div>

      <Button type="submit" className="w-full">
        Enviar Mensaje
      </Button>

      <p className="text-xs text-muted-foreground text-center">
        Al enviar este formulario, aceptas nuestra{' '}
        <a href="/privacy" className="text-primary hover:underline">
          Política de Privacidad
        </a>
      </p>
    </form>
  );
}
