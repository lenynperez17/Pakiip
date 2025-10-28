#!/bin/bash
echo "ANÁLISIS COMPLETO DE COMPROBANTES PDF"
echo "======================================"
echo ""

files=(
  "BoletaCompleto.php"
  "BoletaCompleto_.php"
  "FacturaCompleta.php"
  "FacturaCompleta_.php"
  "Boleta.php"
  "Boleta_.php"
  "Factura.php"
  "Factura_.php"
  "Notapedido.php"
  "NpedidoCompleto.php"
  "Factura_servicio.php"
  "Boleta_servicio.php"
  "Doccobranza.php"
  "Oservicio.php"
  "Cotizacion.php"
  "Boletapago.php"
  "Guia.php"
  "Factura2.php"
)

for file in "${files[@]}"; do
  if [ -f "$file" ]; then
    echo "=== $file ==="
    
    # Buscar addCadreEurosFrancs
    cadre_line=$(grep -n "^function addCadreEurosFrancs(" "$file" 2>/dev/null | head -1 | cut -d: -f1)
    if [ -n "$cadre_line" ]; then
      echo "addCadreEurosFrancs: línea $cadre_line"
      # Extraer altura y y-position
      sed -n "${cadre_line},$((cadre_line+20))p" "$file" | grep -E "y1.*=|y2.*=" | head -2
    else
      echo "addCadreEurosFrancs: NO ENCONTRADA"
    fi
    
    # Buscar addTVAs
    tva_line=$(grep -n "^function addTVAs(" "$file" 2>/dev/null | head -1 | cut -d: -f1)
    if [ -n "$tva_line" ]; then
      echo "addTVAs: línea $tva_line"
      # Ver parámetros
      sed -n "${tva_line}p" "$file"
    else
      echo "addTVAs: NO ENCONTRADA"
    fi
    
    echo ""
  fi
done
