'use client';

import React, { Component, ErrorInfo, ReactNode } from 'react';

interface Props {
  children: ReactNode;
  fallback?: ReactNode;
}

interface State {
  hasError: boolean;
  error?: Error;
}

/**
 * Error Boundary para capturar errores de renderizado en React
 * Evita que errores en componentes hijos crasheen toda la aplicación
 */
export class ErrorBoundary extends Component<Props, State> {
  constructor(props: Props) {
    super(props);
    this.state = { hasError: false };
  }

  static getDerivedStateFromError(error: Error): State {
    console.error('🔴 [ERROR BOUNDARY] Error capturado:', error);
    return { hasError: true, error };
  }

  componentDidCatch(error: Error, errorInfo: ErrorInfo) {
    console.error('🔴 [ERROR BOUNDARY] Error info:', errorInfo);
  }

  render() {
    if (this.state.hasError) {
      if (this.props.fallback) {
        return this.props.fallback;
      }

      return (
        <div className="p-4 border border-red-500 rounded-md bg-red-50 text-red-900">
          <h2 className="text-lg font-semibold mb-2">Error de Renderizado</h2>
          <p className="text-sm">
            Ocurrió un error al mostrar este componente. Por favor, recarga la página.
          </p>
          {this.state.error && (
            <p className="text-xs mt-2 font-mono">
              {this.state.error.message}
            </p>
          )}
          <button
            onClick={() => this.setState({ hasError: false })}
            className="mt-4 px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
          >
            Reintentar
          </button>
        </div>
      );
    }

    return this.props.children;
  }
}
