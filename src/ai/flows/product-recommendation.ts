// src/ai/flows/product-recommendation.ts
'use server';

/**
 * @fileOverview Product recommendation AI agent.
 *
 * - getProductRecommendations - A function that recommends products to users.
 * - ProductRecommendationInput - The input type for the getProductRecommendations function.
 * - ProductRecommendationOutput - The return type for the getProductRecommendations function.
 */

import {ai} from '@/ai/genkit';
import {z} from 'genkit';

const ProductRecommendationInputSchema = z.object({
  availableProducts: z
    .string()
    .describe(
      'Un array JSON de todos los productos disponibles, cada uno con su id, nombre y descripción.'
    ),
  orderHistory: z
    .string()
    .describe('El historial de pedidos pasados del usuario, como una cadena JSON.'),
  currentCart: z
    .string()
    .optional()
    .describe('El carrito de compras actual del usuario, como una cadena JSON.'),
  userPreferences: z
    .string()
    .optional()
    .describe('Las preferencias declaradas por el usuario, como una cadena JSON.'),
  photoDataUri: z
    .string()
    .optional()
    .describe(
      "Una foto de un plato o producto, como un URI de datos que debe incluir un tipo MIME y usar codificación Base64. Formato esperado: 'data:<mimetype>;base64,<encoded_data>'"
    ),
});
export type ProductRecommendationInput = z.infer<
  typeof ProductRecommendationInputSchema
>;

const ProductRecommendationOutputSchema = z.object({
  recommendedProductIds: z
    .string()
    .describe('Un array JSON de IDs de productos recomendados.'),
  reasoning: z
    .string()
    .describe('El razonamiento detrás de las recomendaciones de productos.'),
});

export type ProductRecommendationOutput = z.infer<
  typeof ProductRecommendationOutputSchema
>;

export async function getProductRecommendations(
  input: ProductRecommendationInput
): Promise<ProductRecommendationOutput> {
  return productRecommendationFlow(input);
}

const prompt = ai.definePrompt({
  name: 'productRecommendationPrompt',
  input: {schema: ProductRecommendationInputSchema},
  output: {schema: ProductRecommendationOutputSchema},
  prompt: `Eres un experto en recomendación de productos para un mercado en línea.
  Basado en el historial de pedidos del usuario, el carrito actual, las preferencias declaradas y la lista de productos disponibles,
  recomienda productos que podrían interesarle al usuario. Justifica tu razonamiento.

  Lista de Productos Disponibles (usa los IDs de estos productos para tus recomendaciones):
  {{{availableProducts}}}

  {{#if photoDataUri}}
  Considera esta imagen al hacer tus recomendaciones: {{media url=photoDataUri}}
  {{/if}}

  Historial de Pedidos: {{{orderHistory}}}
  Carrito Actual: {{{currentCart}}}
  Preferencias del Usuario: {{{userPreferences}}}

  Formatea tu salida como un objeto JSON con los campos "recommendedProductIds" y "reasoning".
  El campo recommendedProductIds debe ser un array JSON de IDs de productos.
  Incluye al menos 3 recomendaciones de productos.
  `,
});

const productRecommendationFlow = ai.defineFlow(
  {
    name: 'productRecommendationFlow',
    inputSchema: ProductRecommendationInputSchema,
    outputSchema: ProductRecommendationOutputSchema,
  },
  async input => {
    const {output} = await prompt(input);
    return output!;
  }
);
