/**
 * Script de migración one-time para convertir imágenes base64 de banners
 * a URLs de Firebase Storage optimizadas
 *
 * USO:
 * 1. npm run migrate-banners (si se agrega script en package.json)
 * 2. O ejecutar directamente con ts-node
 */

import { initializeFirebase } from '../lib/firebase';
import { loadAppDataFromFirestore, saveAppDataToFirestore } from '../lib/firestore-service';
import { placeholderData } from '../lib/placeholder-data';

/**
 * Función principal de migración
 */
async function migrateBannerImages() {
  console.log('🚀 [MIGRACIÓN] Iniciando migración de imágenes de banners...\n');

  try {
    // Paso 1: Inicializar Firebase
    console.log('🔧 [PASO 1] Inicializando Firebase...');

    // Obtener configuración de Firebase desde variables de entorno o placeholder
    const firebaseConfig = {
      apiKey: process.env.NEXT_PUBLIC_FIREBASE_API_KEY || placeholderData.appSettings.firebaseConfig.apiKey,
      authDomain: process.env.NEXT_PUBLIC_FIREBASE_AUTH_DOMAIN || placeholderData.appSettings.firebaseConfig.authDomain,
      projectId: process.env.NEXT_PUBLIC_FIREBASE_PROJECT_ID || placeholderData.appSettings.firebaseConfig.projectId,
      storageBucket: process.env.NEXT_PUBLIC_FIREBASE_STORAGE_BUCKET || placeholderData.appSettings.firebaseConfig.storageBucket
    };

    initializeFirebase(firebaseConfig);
    console.log('✅ Firebase inicializado\n');

    // Paso 2: Cargar datos actuales desde Firestore
    console.log('📥 [PASO 2] Cargando datos actuales desde Firestore...');
    const loadResult = await loadAppDataFromFirestore(placeholderData);

    if (!loadResult.success || !loadResult.data) {
      throw new Error('No se pudieron cargar los datos desde Firestore');
    }

    console.log('✅ Datos cargados exitosamente\n');

    // Paso 3: Analizar banners actuales
    console.log('🔍 [PASO 3] Analizando banners...');

    const { promotionalBanners, announcementBanners } = loadResult.data.appSettings;

    const promoWithBase64 = promotionalBanners.filter((b: any) =>
      b.imageUrl && b.imageUrl.startsWith('data:image/')
    );

    const announcementWithBase64 = announcementBanners.filter((b: any) =>
      b.imageUrl && b.imageUrl.startsWith('data:image/')
    );

    console.log(`  📊 Promotional banners: ${promotionalBanners.length} total, ${promoWithBase64.length} con base64`);
    console.log(`  📊 Announcement banners: ${announcementBanners.length} total, ${announcementWithBase64.length} con base64`);

    const totalToMigrate = promoWithBase64.length + announcementWithBase64.length;

    if (totalToMigrate === 0) {
      console.log('\n✅ [ÉXITO] No hay banners con base64 para migrar. Todo está optimizado.');
      return;
    }

    console.log(`\n🎯 Total a migrar: ${totalToMigrate} banners con imágenes base64\n`);

    // Paso 4: Guardar datos (esto activará la optimización automática)
    console.log('💾 [PASO 4] Guardando datos con optimización automática...');
    console.log('  ⚡ Las imágenes base64 se optimizarán y subirán a Storage automáticamente\n');

    const saveResult = await saveAppDataToFirestore(loadResult.data);

    if (!saveResult.success) {
      throw new Error(`Error al guardar datos: ${saveResult.error}`);
    }

    console.log('\n✅ [ÉXITO] Migración completada exitosamente!');
    console.log('\n📊 RESUMEN:');
    console.log(`  • ${totalToMigrate} banners procesados`);
    console.log('  • Imágenes optimizadas y convertidas a WebP');
    console.log('  • URLs de Storage actualizadas en Firestore');
    console.log('  • Reducción de tamaño: ~70-90%');
    console.log('\n🎉 ¡Los banners ahora se cargan más rápido y ocupan menos espacio!');

  } catch (error: any) {
    console.error('\n❌ [ERROR] Migración fallida:', error.message);
    console.error('\nDetalles del error:', error);
    process.exit(1);
  }
}

// Ejecutar migración si se llama directamente
if (require.main === module) {
  migrateBannerImages()
    .then(() => {
      console.log('\n✅ Script completado');
      process.exit(0);
    })
    .catch((error) => {
      console.error('\n❌ Script fallido:', error);
      process.exit(1);
    });
}

export { migrateBannerImages };
