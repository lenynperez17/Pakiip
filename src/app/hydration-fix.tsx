'use client';

import { useEffect } from 'react';

/**
 * Componente para limpiar atributos inyectados por extensiones del navegador
 * antes de la hidratación de React, evitando advertencias de hidratación.
 */
export function HydrationFix() {
  useEffect(() => {
    // Lista de atributos comunes inyectados por extensiones
    const extensionAttributes = [
      'bis_register',
      'bis_skin_checked',
      '__processed_',
      'data-lastpass-',
      'data-1p-',
      'data-dashlane-',
      'gramm',
      'data-gr-ext-installed',
    ];

    // Función para limpiar atributos de un elemento
    const cleanElement = (element: Element) => {
      extensionAttributes.forEach(attr => {
        // Remover atributos exactos
        if (element.hasAttribute(attr)) {
          element.removeAttribute(attr);
        }

        // Remover atributos que comienzan con el prefijo
        Array.from(element.attributes).forEach(attribute => {
          if (attribute.name.startsWith(attr)) {
            element.removeAttribute(attribute.name);
          }
        });
      });
    };

    // Limpiar todo el documento
    const cleanAllElements = () => {
      // Limpiar body
      if (document.body) {
        cleanElement(document.body);

        // Limpiar todos los descendientes
        const allElements = document.body.querySelectorAll('*');
        allElements.forEach(cleanElement);
      }
    };

    // Ejecutar limpieza inicial
    cleanAllElements();

    // Observar y limpiar atributos dinámicos
    if (typeof MutationObserver !== 'undefined') {
      const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
          if (mutation.type === 'attributes') {
            const target = mutation.target as Element;
            const attrName = mutation.attributeName;
            if (attrName) {
              // Verificar si el atributo es de una extensión
              const isExtensionAttr = extensionAttributes.some(
                prefix => attrName.startsWith(prefix)
              );
              if (isExtensionAttr && target.hasAttribute(attrName)) {
                target.removeAttribute(attrName);
              }
            }
          }
          // Si se agregan nuevos nodos, limpiarlos también
          if (mutation.type === 'childList') {
            mutation.addedNodes.forEach((node) => {
              if (node.nodeType === Node.ELEMENT_NODE) {
                cleanElement(node as Element);
              }
            });
          }
        });
      });

      observer.observe(document.body, {
        attributes: true,
        attributeOldValue: true,
        childList: true,
        subtree: true,
      });

      // Cleanup
      return () => observer.disconnect();
    }
  }, []);

  return null;
}
