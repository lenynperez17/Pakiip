<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:output method="html" version="4.0" encoding="UTF-8" indent="yes"/>
  <xsl:template match="/">
    <html>
      <head>
        <style>
          /* Estilos CSS para el HTML generado a partir del XML */
          /* Ejemplo: */
          /* .elemento { color: red; } */
        </style>
      </head>
      <body>
        <xsl:apply-templates/>
      </body>
    </html>
  </xsl:template>
  <xsl:template match="*">
    <!-- Plantilla para los elementos del XML -->
    <!-- Ejemplo: -->
    <!-- <div class="elemento"> -->
    <xsl:apply-templates/>
    <!-- </div> -->
  </xsl:template>
  <xsl:template match="text()">
    <!-- Plantilla para el contenido de los elementos del XML -->
    <xsl:value-of select="."/>
  </xsl:template>
</xsl:stylesheet>
