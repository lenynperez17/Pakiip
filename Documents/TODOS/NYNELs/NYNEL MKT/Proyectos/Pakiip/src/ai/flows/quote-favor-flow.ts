'use server';

/**
 * @fileOverview An AI agent for quoting custom user requests ("favors").
 *
 * - quoteFavor - A function that calculates a service and shipping fee for a custom request.
 * - QuoteFavorInput - The input type for the quoteFavor function.
 * - QuoteFavorOutput - The return type for the quoteFavor function.
 */

import {ai} from '@/ai/genkit';
import {z} from 'genkit';

const QuoteFavorInputSchema = z.object({
  description: z
    .string()
    .describe('A detailed description of the favor the user is asking for.'),
  estimatedProductCost: z
    .number()
    .describe('The estimated cost of the products the user wants to buy.'),
  distanceKm: z.number().describe('The distance in kilometers between pickup and delivery.'),
  photoDataUri: z
    .string()
    .optional()
    .describe(
      "A photo of an item related to the request, as a data URI. Format: 'data:<mimetype>;base64,<encoded_data>'."
    ),
});
export type QuoteFavorInput = z.infer<typeof QuoteFavorInputSchema>;

const QuoteFavorOutputSchema = z.object({
  serviceFee: z
    .number()
    .describe('The calculated service fee for performing the favor.'),
  shippingFee: z
    .number()
    .describe('The calculated shipping fee based on the distance.'),
  totalFee: z.number().describe('The total fee (service + shipping).'),
  reasoning: z
    .string()
    .describe('The reasoning behind the calculated fees.'),
});
export type QuoteFavorOutput = z.infer<typeof QuoteFavorOutputSchema>;

export async function quoteFavor(
  input: QuoteFavorInput
): Promise<QuoteFavorOutput> {
  return quoteFavorFlow(input);
}

const prompt = ai.definePrompt({
  name: 'quoteFavorPrompt',
  input: {schema: QuoteFavorInputSchema},
  output: {schema: QuoteFavorOutputSchema},
  prompt: `You are an AI assistant for a delivery app. Your task is to calculate a fair service and shipping fee for a custom user request ("favor").

Base your calculations on the following:
- Shipping Fee: Charge a base of $3 and add $0.75 per kilometer.
- Service Fee: This should be a percentage of the estimated product cost. Use a base of 10%, but adjust it based on the complexity of the request described by the user. For very simple tasks (e.g., "pick up a package"), it can be as low as 5%. For complex tasks (e.g., "go to three different stores to find a specific item"), it can be up to 20%.

User Request Details:
- Description: {{{description}}}
- Estimated Product Cost: {{{estimatedProductCost}}}
- Distance: {{{distanceKm}}} km
{{#if photoDataUri}}
- Photo provided: {{media url=photoDataUri}}
{{/if}}

Please calculate the 'serviceFee', 'shippingFee', and 'totalFee'. Provide a brief 'reasoning' explaining how you arrived at the service fee based on the request's complexity.
`,
});

const quoteFavorFlow = ai.defineFlow(
  {
    name: 'quoteFavorFlow',
    inputSchema: QuoteFavorInputSchema,
    outputSchema: QuoteFavorOutputSchema,
  },
  async input => {
    const {output} = await prompt(input);
    return output!;
  }
);
