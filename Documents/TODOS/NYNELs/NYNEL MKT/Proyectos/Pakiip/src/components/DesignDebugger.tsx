"use client";

import { useEffect, useState } from 'react';

/**
 * 🐛 Componente de Debug Visual para verificar diseño responsive
 * Muestra información en tiempo real sobre el ancho de pantalla y breakpoints
 */
export function DesignDebugger() {
  const [windowWidth, setWindowWidth] = useState(0);
  const [containerWidth, setContainerWidth] = useState(0);
  const [debugInfo, setDebugInfo] = useState<any[]>([]);

  useEffect(() => {
    // Función para actualizar dimensiones
    const updateDimensions = () => {
      setWindowWidth(window.innerWidth);

      // Analizar TODOS los elementos que podrían estar causando el problema
      const elements = [
        { name: '🎯 div.relative.flex (LayoutWrapper padre)', selector: 'div.relative.flex.min-h-screen' },
        { name: 'main', selector: 'main' },
        { name: 'header', selector: 'header' },
        { name: 'header > div (inner)', selector: 'header > div' },
        { name: 'body', selector: 'body' },
        { name: 'html', selector: 'html' },
        { name: 'main > div', selector: 'main > div' },
        { name: 'section tiendas', selector: 'section[aria-label*="Tiendas"]' },
      ];

      const info: any[] = [];
      elements.forEach(({ name, selector }) => {
        const el = document.querySelector(selector);
        if (el) {
          const computed = window.getComputedStyle(el);
          const rect = el.getBoundingClientRect();

          // Calcular ancho incluyendo padding
          const paddingLeft = parseFloat(computed.paddingLeft);
          const paddingRight = parseFloat(computed.paddingRight);
          const contentWidth = rect.width;
          const totalWidth = contentWidth;

          // Obtener información del padre
          let parentInfo = 'Sin padre';
          if (el.parentElement) {
            const parentRect = el.parentElement.getBoundingClientRect();
            const parentComputed = window.getComputedStyle(el.parentElement);
            parentInfo = `${Math.round(parentRect.width)}px (max: ${parentComputed.maxWidth})`;
          }

          info.push({
            name,
            width: contentWidth,
            maxWidth: computed.maxWidth,
            minWidth: computed.minWidth,
            boxSizing: computed.boxSizing,
            paddingLeft: computed.paddingLeft,
            paddingRight: computed.paddingRight,
            marginLeft: computed.marginLeft,
            marginRight: computed.marginRight,
            totalWidthWithPadding: Math.round(totalWidth),
            parentWidth: parentInfo,
            display: computed.display,
            position: computed.position,
            classes: el.className,
            hasMaxW7xl: el.className.includes('max-w-7xl'),
            hasWFull: el.className.includes('w-full'),
            // Información adicional crítica
            clientWidth: (el as HTMLElement).clientWidth,
            offsetWidth: (el as HTMLElement).offsetWidth,
            scrollWidth: (el as HTMLElement).scrollWidth,
          });
        }
      });

      setDebugInfo(info);

      // Obtener el ancho del main
      const mainContainer = document.querySelector('main');
      if (mainContainer) {
        setContainerWidth(mainContainer.offsetWidth);
      }
    };

    // Actualizar al montar
    updateDimensions();

    // Actualizar al redimensionar
    window.addEventListener('resize', updateDimensions);

    return () => window.removeEventListener('resize', updateDimensions);
  }, []);

  // Determinar breakpoint actual
  const getBreakpoint = (width: number) => {
    if (width < 640) return 'Mobile (< 640px)';
    if (width < 768) return 'SM (640px - 768px)';
    if (width < 1024) return 'MD (768px - 1024px)';
    if (width < 1280) return 'LG (1024px - 1280px)';
    if (width < 1536) return 'XL (1280px - 1536px)';
    return '2XL (≥ 1536px)';
  };

  // Verificar si el contenido está limitado correctamente
  const isContentLimited = containerWidth <= 1600;
  const limitStatus = containerWidth > 1600 ? '❌ NO LIMITADO' : '✅ LIMITADO';

  return (
    <div
      className="fixed bottom-4 right-4 z-50 bg-black/95 text-white p-4 rounded-lg shadow-2xl font-mono text-xs max-w-2xl max-h-[80vh] overflow-auto"
      style={{ backdropFilter: 'blur(10px)' }}
    >
      <div className="font-bold text-green-400 mb-3 text-sm">🎨 DEBUG DISEÑO RESPONSIVE COMPLETO</div>

      <div className="space-y-1 mb-4">
        <div className="border-b border-gray-700 pb-1 mb-2">
          <span className="text-gray-400">Breakpoint:</span>
          <span className="ml-2 text-yellow-400 font-bold">{getBreakpoint(windowWidth)}</span>
        </div>

        <div>
          <span className="text-gray-400">Ancho ventana:</span>
          <span className="ml-2 text-blue-400">{windowWidth}px</span>
        </div>

        <div>
          <span className="text-gray-400">Ancho contenedor MAIN:</span>
          <span className="ml-2 text-blue-400 font-bold">{containerWidth}px</span>
        </div>

        <div>
          <span className="text-gray-400">Max width esperado:</span>
          <span className="ml-2 text-purple-400">1600px (diseño balanceado)</span>
        </div>

        <div className="border-t border-gray-700 pt-2 mt-2">
          <span className="text-gray-400">Estado:</span>
          <span className={`ml-2 font-bold ${isContentLimited ? 'text-green-400' : 'text-red-400'}`}>
            {limitStatus}
          </span>
        </div>

        {!isContentLimited && windowWidth > 1600 && (
          <div className="mt-2 p-2 bg-red-900/50 rounded text-red-200 text-xs">
            ⚠️ El contenido está ocupando más de 1600px en pantallas grandes
          </div>
        )}

        {isContentLimited && windowWidth > 1600 && (
          <div className="mt-2 p-2 bg-green-900/50 rounded text-green-200 text-xs">
            ✅ Diseño correcto: contenido limitado a 1600px
          </div>
        )}
      </div>

      {/* ANÁLISIS DETALLADO DE ELEMENTOS */}
      <div className="border-t border-gray-700 pt-3 mt-3">
        <div className="font-bold text-cyan-400 mb-2">🔍 ANÁLISIS DE ELEMENTOS:</div>
        <div className="space-y-2">
          {debugInfo.map((info, index) => (
            <div key={index} className="bg-gray-800/50 p-2 rounded text-[10px] border border-gray-700">
              <div className="font-bold text-yellow-300 mb-1">{info.name}</div>

              {/* MEDIDAS PRINCIPALES */}
              <div className="bg-gray-900/50 p-1 rounded mb-1">
                <div className="text-gray-300 font-bold">📏 MEDIDAS:</div>
                <div className="text-gray-400">
                  getBoundingClientRect: <span className={info.width > 1600 ? 'text-red-400' : 'text-green-400'}>{Math.round(info.width)}px</span>
                </div>
                <div className="text-gray-400">
                  clientWidth: <span className="text-cyan-300">{info.clientWidth}px</span>
                </div>
                <div className="text-gray-400">
                  offsetWidth: <span className="text-cyan-300">{info.offsetWidth}px</span>
                </div>
                <div className="text-gray-400">
                  scrollWidth: <span className="text-cyan-300">{info.scrollWidth}px</span>
                </div>
              </div>

              {/* CSS CONSTRAINTS */}
              <div className="bg-gray-900/50 p-1 rounded mb-1">
                <div className="text-gray-300 font-bold">🎨 CSS:</div>
                <div className="text-gray-400">
                  max-width: <span className={info.maxWidth === '1600px' ? 'text-green-400' : 'text-blue-300'}>{info.maxWidth}</span>
                </div>
                <div className="text-gray-400">
                  min-width: <span className="text-blue-300">{info.minWidth}</span>
                </div>
                <div className="text-gray-400">
                  box-sizing: <span className="text-purple-300">{info.boxSizing}</span>
                </div>
                <div className="text-gray-400">
                  display: <span className="text-purple-300">{info.display}</span>
                </div>
                <div className="text-gray-400">
                  position: <span className="text-purple-300">{info.position}</span>
                </div>
              </div>

              {/* SPACING */}
              <div className="bg-gray-900/50 p-1 rounded mb-1">
                <div className="text-gray-300 font-bold">📦 SPACING:</div>
                <div className="text-gray-400">
                  Padding L/R: <span className="text-cyan-300">{info.paddingLeft} / {info.paddingRight}</span>
                </div>
                <div className="text-gray-400">
                  Margin L/R: <span className="text-cyan-300">{info.marginLeft} / {info.marginRight}</span>
                </div>
              </div>

              {/* PARENT INFO */}
              <div className="bg-gray-900/50 p-1 rounded mb-1">
                <div className="text-gray-300 font-bold">👨‍👦 PADRE:</div>
                <div className="text-gray-400">
                  Ancho padre: <span className="text-orange-300">{info.parentWidth}</span>
                </div>
              </div>

              {/* FLAGS */}
              <div className="text-gray-400 text-[9px]">
                max-w-7xl: <span className={info.hasMaxW7xl ? 'text-green-400' : 'text-red-400'}>{info.hasMaxW7xl ? 'SÍ ✅' : 'NO ❌'}</span> |
                w-full: <span className={info.hasWFull ? 'text-red-400' : 'text-green-400'}>{info.hasWFull ? 'SÍ ⚠️' : 'NO ✅'}</span>
              </div>

              {info.classes && (
                <div className="text-gray-500 text-[9px] mt-1 break-all border-t border-gray-700 pt-1">
                  Classes: {info.classes.substring(0, 80)}{info.classes.length > 80 ? '...' : ''}
                </div>
              )}
            </div>
          ))}
        </div>
      </div>

      <div className="text-[10px] text-gray-500 mt-3 pt-2 border-t border-gray-700">
        💡 Este debug se oculta en producción
      </div>
    </div>
  );
}
