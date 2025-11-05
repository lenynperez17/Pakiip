"use client";

import React, { useState, useEffect } from 'react';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Loader2, MapPin } from 'lucide-react';
import { searchPlaces, GeocodeResult } from '@/lib/google-geocoding';

interface AddressAutocompleteProps {
  label?: string;
  placeholder?: string;
  value: string;
  onChange: (value: string) => void;
  onSelectAddress?: (result: GeocodeResult) => void;
  required?: boolean;
  id?: string;
}

export function AddressAutocomplete({
  label = "Dirección",
  placeholder = "Busca tu dirección...",
  value,
  onChange,
  onSelectAddress,
  required = false,
  id = "address"
}: AddressAutocompleteProps) {
  const [suggestions, setSuggestions] = useState<GeocodeResult[]>([]);
  const [isSearching, setIsSearching] = useState(false);
  const [showSuggestions, setShowSuggestions] = useState(false);

  // Debounce para búsqueda
  useEffect(() => {
    if (value.length < 3) {
      setSuggestions([]);
      return;
    }

    const timeoutId = setTimeout(() => {
      searchAddress(value);
    }, 500);

    return () => clearTimeout(timeoutId);
  }, [value]);

  const searchAddress = async (query: string) => {
    setIsSearching(true);
    setShowSuggestions(true);

    try {
      const results = await searchPlaces(query);
      setSuggestions(results);
    } catch (error) {
      console.error("Error buscando direcciones:", error);
      setSuggestions([]);
    } finally {
      setIsSearching(false);
    }
  };

  const handleSelectAddress = (result: GeocodeResult) => {
    onChange(result.fullAddress);
    setSuggestions([]);
    setShowSuggestions(false);

    if (onSelectAddress) {
      onSelectAddress(result);
    }
  };

  return (
    <div className="space-y-2 relative">
      {label && (
        <Label htmlFor={id}>
          {label} {required && <span className="text-destructive">*</span>}
        </Label>
      )}

      <div className="relative">
        <MapPin className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
        <Input
          id={id}
          type="text"
          placeholder={placeholder}
          value={value}
          onChange={(e) => onChange(e.target.value)}
          onFocus={() => {
            if (value.length >= 3 && suggestions.length > 0) {
              setShowSuggestions(true);
            }
          }}
          onBlur={() => setTimeout(() => setShowSuggestions(false), 200)}
          required={required}
          className="pl-10"
        />
        {isSearching && (
          <Loader2 className="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 animate-spin text-muted-foreground" />
        )}
      </div>

      {/* Sugerencias */}
      {showSuggestions && suggestions.length > 0 && (
        <div className="absolute z-50 w-full mt-1 bg-background border rounded-lg shadow-lg max-h-[300px] overflow-y-auto">
          {suggestions.map((result, index) => (
            <button
              key={`${result.coordinates.lat}-${result.coordinates.lng}-${index}`}
              type="button"
              onClick={() => handleSelectAddress(result)}
              className="w-full text-left p-3 hover:bg-accent transition-colors border-b last:border-b-0"
            >
              <div className="flex items-start gap-2">
                <MapPin className="h-4 w-4 mt-1 flex-shrink-0 text-primary" />
                <div className="flex-1 min-w-0">
                  <p className="font-medium text-sm">
                    {result.formattedAddress}
                  </p>
                  <p className="text-xs text-muted-foreground truncate">
                    {result.fullAddress}
                  </p>
                </div>
              </div>
            </button>
          ))}
        </div>
      )}

      {/* Mensaje cuando no hay resultados */}
      {showSuggestions && value.length >= 3 && !isSearching && suggestions.length === 0 && (
        <div className="absolute z-50 w-full mt-1 bg-background border rounded-lg shadow-lg p-4">
          <p className="text-sm text-muted-foreground text-center">
            No se encontraron resultados para &quot;{value}&quot;
          </p>
        </div>
      )}
    </div>
  );
}
