// Configuración de Firebase para autenticación, almacenamiento y base de datos
import { initializeApp, getApps, FirebaseApp } from 'firebase/app';
import {
  getAuth,
  GoogleAuthProvider,
  signInWithPopup,
  signInWithEmailAndPassword,
  RecaptchaVerifier,
  signInWithPhoneNumber,
  ConfirmationResult,
  Auth,
  linkWithCredential,
  PhoneAuthProvider,
  linkWithPopup
} from 'firebase/auth';
import { getStorage, ref, uploadBytes, getDownloadURL, deleteObject, FirebaseStorage } from 'firebase/storage';
import {
  getFirestore,
  doc,
  getDoc,
  setDoc,
  updateDoc,
  deleteDoc,
  collection,
  getDocs,
  query,
  where,
  Firestore,
  DocumentData,
  onSnapshot,
  Unsubscribe
} from 'firebase/firestore';

let app: FirebaseApp | undefined;
let auth: Auth | undefined;
let storage: FirebaseStorage | undefined;
let firestore: Firestore | undefined;

/**
 * Inicializa Firebase con la configuración proporcionada
 * @param config Configuración de Firebase desde appSettings
 */
export function initializeFirebase(config: {
  apiKey: string;
  authDomain: string;
  projectId: string;
  storageBucket?: string;
}) {
  // Evitar inicializar múltiples veces
  if (getApps().length > 0) {
    app = getApps()[0];
    auth = getAuth(app);
    storage = getStorage(app);
    firestore = getFirestore(app);
    return { app, auth, storage, firestore };
  }

  try {
    app = initializeApp(config);
    auth = getAuth(app);
    storage = getStorage(app);
    firestore = getFirestore(app);
    return { app, auth, storage, firestore };
  } catch (error) {
    console.error('Error al inicializar Firebase:', error);
    throw error;
  }
}

/**
 * Obtiene la instancia de Auth de Firebase
 */
export function getFirebaseAuth(): Auth {
  if (!auth) {
    throw new Error('Firebase no ha sido inicializado. Llama a initializeFirebase primero.');
  }
  return auth;
}

/**
 * Inicia sesión con Google usando popup
 */
export async function signInWithGoogle() {
  try {
    const auth = getFirebaseAuth();
    const provider = new GoogleAuthProvider();
    provider.addScope('profile');
    provider.addScope('email');

    const result = await signInWithPopup(auth, provider);

    return {
      success: true,
      user: {
        uid: result.user.uid,
        email: result.user.email,
        displayName: result.user.displayName,
        photoURL: result.user.photoURL,
      }
    };
  } catch (error: any) {
    console.error('Error en Google Sign In:', error);
    return {
      success: false,
      error: error.message || 'Error desconocido al iniciar sesión con Google'
    };
  }
}

/**
 * Inicia sesión con email y contraseña
 * @param email Email del usuario
 * @param password Contraseña del usuario
 */
export async function signInWithEmailPassword(email: string, password: string) {
  try {
    const auth = getFirebaseAuth();
    const result = await signInWithEmailAndPassword(auth, email, password);

    return {
      success: true,
      user: {
        uid: result.user.uid,
        email: result.user.email,
        displayName: result.user.displayName,
        photoURL: result.user.photoURL,
      }
    };
  } catch (error: any) {
    console.error('Error en Email/Password Sign In:', error);

    // Traducir mensajes de error comunes
    let errorMessage = error.message || 'Error desconocido al iniciar sesión';
    if (error.code === 'auth/user-not-found') {
      errorMessage = 'No existe una cuenta con este email';
    } else if (error.code === 'auth/wrong-password') {
      errorMessage = 'Contraseña incorrecta';
    } else if (error.code === 'auth/invalid-email') {
      errorMessage = 'Email inválido';
    } else if (error.code === 'auth/user-disabled') {
      errorMessage = 'Esta cuenta ha sido deshabilitada';
    } else if (error.code === 'auth/too-many-requests') {
      errorMessage = 'Demasiados intentos fallidos. Intenta más tarde';
    }

    return {
      success: false,
      error: errorMessage
    };
  }
}

/**
 * Configura el reCAPTCHA Enterprise para autenticación por teléfono
 * @param containerId ID del contenedor donde se mostrará el reCAPTCHA
 */
export function setupRecaptcha(containerId: string): RecaptchaVerifier {
  const auth = getFirebaseAuth();

  // Configuración de reCAPTCHA Enterprise
  return new RecaptchaVerifier(auth, containerId, {
    size: 'invisible',
    callback: () => {
      console.log('✅ reCAPTCHA Enterprise resuelto');
    },
    'expired-callback': () => {
      console.warn('⚠️ reCAPTCHA Enterprise expirado');
    }
  });
}

/**
 * Envía código de verificación al número de teléfono
 * @param phoneNumber Número de teléfono con código de país (ej: +51999999999)
 * @param recaptchaVerifier Verificador de reCAPTCHA
 */
export async function sendPhoneVerificationCode(
  phoneNumber: string,
  recaptchaVerifier: RecaptchaVerifier
): Promise<{ success: boolean; confirmationResult?: ConfirmationResult; error?: string }> {
  try {
    const auth = getFirebaseAuth();
    const confirmationResult = await signInWithPhoneNumber(auth, phoneNumber, recaptchaVerifier);

    return {
      success: true,
      confirmationResult
    };
  } catch (error: any) {
    console.error('Error al enviar código de verificación:', error);
    return {
      success: false,
      error: error.message || 'Error al enviar código de verificación'
    };
  }
}

/**
 * Verifica el código enviado al teléfono
 * @param confirmationResult Resultado de confirmación del envío del código
 * @param code Código de verificación ingresado por el usuario
 */
export async function verifyPhoneCode(
  confirmationResult: ConfirmationResult,
  code: string
) {
  try {
    const result = await confirmationResult.confirm(code);

    return {
      success: true,
      user: {
        uid: result.user.uid,
        phoneNumber: result.user.phoneNumber,
        displayName: result.user.displayName,
      }
    };
  } catch (error: any) {
    console.error('Error al verificar código:', error);
    return {
      success: false,
      error: error.message || 'Código de verificación inválido'
    };
  }
}

/**
 * Cierra la sesión del usuario actual
 */
export async function signOutUser() {
  try {
    const auth = getFirebaseAuth();
    await auth.signOut();
    return { success: true };
  } catch (error: any) {
    console.error('Error al cerrar sesión:', error);
    return {
      success: false,
      error: error.message || 'Error al cerrar sesión'
    };
  }
}

/**
 * Obtiene la instancia de Storage de Firebase
 */
export function getFirebaseStorage(): FirebaseStorage {
  if (!storage) {
    throw new Error('Firebase Storage no ha sido inicializado. Llama a initializeFirebase primero.');
  }
  return storage;
}

/**
 * Sube una imagen a Firebase Storage y retorna la URL de descarga
 * @param file File o Blob de la imagen
 * @param path Ruta donde se guardará la imagen (ej: 'settings/logo.png')
 * @returns URL pública de la imagen subida
 */
export async function uploadImage(file: File | Blob, path: string): Promise<{ success: boolean; url?: string; error?: string }> {
  try {
    const storage = getFirebaseStorage();
    const storageRef = ref(storage, path);

    // Subir el archivo
    const snapshot = await uploadBytes(storageRef, file);

    // Obtener la URL de descarga
    const downloadURL = await getDownloadURL(snapshot.ref);

    return {
      success: true,
      url: downloadURL
    };
  } catch (error: any) {
    console.error('Error al subir imagen:', error);
    return {
      success: false,
      error: error.message || 'Error al subir imagen'
    };
  }
}

/**
 * Sube una imagen desde Base64 a Firebase Storage
 * @param base64String String Base64 de la imagen (con o sin prefijo data:image...)
 * @param path Ruta donde se guardará la imagen
 * @returns URL pública de la imagen subida
 */
export async function uploadImageFromBase64(base64String: string, path: string): Promise<{ success: boolean; url?: string; error?: string }> {
  try {
    // Extraer el tipo MIME y los datos Base64
    let mimeType = 'image/jpeg';
    let base64Data = base64String;

    if (base64String.includes('data:')) {
      const matches = base64String.match(/data:([^;]+);base64,(.+)/);
      if (matches) {
        mimeType = matches[1];
        base64Data = matches[2];
      }
    }

    // Convertir Base64 a Blob
    const byteCharacters = atob(base64Data);
    const byteNumbers = new Array(byteCharacters.length);
    for (let i = 0; i < byteCharacters.length; i++) {
      byteNumbers[i] = byteCharacters.charCodeAt(i);
    }
    const byteArray = new Uint8Array(byteNumbers);
    const blob = new Blob([byteArray], { type: mimeType });

    // Subir el blob
    return await uploadImage(blob, path);
  } catch (error: any) {
    console.error('Error al procesar imagen Base64:', error);
    return {
      success: false,
      error: error.message || 'Error al procesar imagen Base64'
    };
  }
}

/**
 * Elimina una imagen de Firebase Storage
 * @param path Ruta de la imagen a eliminar
 */
export async function deleteImage(path: string): Promise<{ success: boolean; error?: string }> {
  try {
    const storage = getFirebaseStorage();
    const storageRef = ref(storage, path);
    await deleteObject(storageRef);

    return { success: true };
  } catch (error: any) {
    console.error('Error al eliminar imagen:', error);
    return {
      success: false,
      error: error.message || 'Error al eliminar imagen'
    };
  }
}

// ============================================================================
// FUNCIONES DE FIRESTORE - BASE DE DATOS
// ============================================================================

/**
 * Obtiene la instancia de Firestore
 */
export function getFirebaseFirestore(): Firestore {
  if (!firestore) {
    throw new Error('Firestore no ha sido inicializado. Llama a initializeFirebase primero.');
  }
  return firestore;
}

/**
 * Sanitiza datos para que sean compatibles con Firestore
 * Elimina funciones, undefined, y convierte tipos incompatibles
 */
function sanitizeDataForFirestore(data: any): any {
  if (data === null || data === undefined) {
    return null;
  }

  if (typeof data === 'function') {
    return null;
  }

  if (data instanceof Date) {
    return data;
  }

  if (Array.isArray(data)) {
    return data.map(item => sanitizeDataForFirestore(item)).filter(item => item !== null);
  }

  if (typeof data === 'object') {
    const sanitized: any = {};
    for (const key in data) {
      if (data.hasOwnProperty(key)) {
        const value = sanitizeDataForFirestore(data[key]);
        if (value !== null && value !== undefined) {
          sanitized[key] = value;
        }
      }
    }
    return sanitized;
  }

  return data;
}

/**
 * Guarda o actualiza un documento en Firestore
 * @param collectionName Nombre de la colección
 * @param docId ID del documento
 * @param data Datos a guardar
 */
export async function saveDocument(
  collectionName: string,
  docId: string,
  data: DocumentData
): Promise<{ success: boolean; error?: string }> {
  try {
    const db = getFirebaseFirestore();
    const docRef = doc(db, collectionName, docId);

    const sanitizedData = sanitizeDataForFirestore(data);

    await setDoc(docRef, sanitizedData, { merge: true });
    return { success: true };
  } catch (error: any) {
    console.error(`Error al guardar documento en ${collectionName}:`, error);
    return {
      success: false,
      error: error.message || 'Error al guardar documento'
    };
  }
}

/**
 * Obtiene un documento de Firestore
 * @param collectionName Nombre de la colección
 * @param docId ID del documento
 */
export async function getDocument<T = DocumentData>(
  collectionName: string,
  docId: string
): Promise<{ success: boolean; data?: T; error?: string }> {
  try {
    const db = getFirebaseFirestore();
    const docRef = doc(db, collectionName, docId);
    const docSnap = await getDoc(docRef);

    if (docSnap.exists()) {
      return {
        success: true,
        data: docSnap.data() as T
      };
    } else {
      return {
        success: false,
        error: 'Documento no encontrado'
      };
    }
  } catch (error: any) {
    console.error(`Error al obtener documento de ${collectionName}:`, error);
    return {
      success: false,
      error: error.message || 'Error al obtener documento'
    };
  }
}

/**
 * Elimina un documento de Firestore
 * @param collectionName Nombre de la colección
 * @param docId ID del documento
 */
export async function deleteDocument(
  collectionName: string,
  docId: string
): Promise<{ success: boolean; error?: string }> {
  try {
    const db = getFirebaseFirestore();
    const docRef = doc(db, collectionName, docId);
    await deleteDoc(docRef);
    return { success: true };
  } catch (error: any) {
    console.error(`Error al eliminar documento de ${collectionName}:`, error);
    return {
      success: false,
      error: error.message || 'Error al eliminar documento'
    };
  }
}

/**
 * Obtiene todos los documentos de una colección
 * @param collectionName Nombre de la colección
 */
export async function getAllDocuments<T = DocumentData>(
  collectionName: string
): Promise<{ success: boolean; data?: T[]; error?: string }> {
  try {
    const db = getFirebaseFirestore();
    const querySnapshot = await getDocs(collection(db, collectionName));
    const documents: T[] = [];

    querySnapshot.forEach((doc) => {
      documents.push({ id: doc.id, ...doc.data() } as T);
    });

    return {
      success: true,
      data: documents
    };
  } catch (error: any) {
    console.error(`Error al obtener documentos de ${collectionName}:`, error);
    return {
      success: false,
      error: error.message || 'Error al obtener documentos'
    };
  }
}

/**
 * Suscribe a cambios en tiempo real de un documento
 * @param collectionName Nombre de la colección
 * @param docId ID del documento
 * @param callback Función a ejecutar cuando cambie el documento
 * @returns Función para cancelar la suscripción
 */
export function subscribeToDocument<T = DocumentData>(
  collectionName: string,
  docId: string,
  callback: (data: T | null) => void
): Unsubscribe {
  const db = getFirebaseFirestore();
  const docRef = doc(db, collectionName, docId);

  return onSnapshot(docRef, (docSnap) => {
    if (docSnap.exists()) {
      callback(docSnap.data() as T);
    } else {
      callback(null);
    }
  }, (error) => {
    console.error(`Error en suscripción a ${collectionName}/${docId}:`, error);
    callback(null);
  });
}

/**
 * Suscribe a cambios en tiempo real de una colección completa
 * @param collectionName Nombre de la colección
 * @param callback Función a ejecutar cuando cambien los documentos
 * @returns Función para cancelar la suscripción
 */
export function subscribeToCollection<T = DocumentData>(
  collectionName: string,
  callback: (data: T[]) => void
): Unsubscribe {
  const db = getFirebaseFirestore();
  const collectionRef = collection(db, collectionName);

  return onSnapshot(collectionRef, (querySnapshot) => {
    const documents: T[] = [];
    querySnapshot.forEach((doc) => {
      documents.push({ id: doc.id, ...doc.data() } as T);
    });
    callback(documents);
  }, (error) => {
    console.error(`Error en suscripción a ${collectionName}:`, error);
    callback([]);
  });
}

// ============================================================================
// FUNCIONES DE ACCOUNT LINKING - VINCULAR GOOGLE Y TELÉFONO
// ============================================================================

/**
 * Vincula la cuenta actual con Google
 * Permite asociar inicio de sesión con Google a una cuenta existente
 */
export async function linkAccountWithGoogle() {
  try {
    const auth = getFirebaseAuth();
    const currentUser = auth.currentUser;

    if (!currentUser) {
      return {
        success: false,
        error: 'No hay usuario autenticado para vincular'
      };
    }

    const provider = new GoogleAuthProvider();
    provider.addScope('profile');
    provider.addScope('email');

    // Vincular con popup (método moderno 2025)
    const result = await linkWithPopup(currentUser, provider);

    return {
      success: true,
      user: {
        uid: result.user.uid,
        email: result.user.email,
        displayName: result.user.displayName,
        photoURL: result.user.photoURL,
        phoneNumber: result.user.phoneNumber
      }
    };
  } catch (error: any) {
    console.error('Error al vincular con Google:', error);

    // Manejar errores específicos
    let errorMessage = error.message || 'Error al vincular cuenta con Google';
    if (error.code === 'auth/credential-already-in-use') {
      errorMessage = 'Esta cuenta de Google ya está vinculada a otro usuario';
    } else if (error.code === 'auth/provider-already-linked') {
      errorMessage = 'Esta cuenta ya está vinculada con Google';
    } else if (error.code === 'auth/email-already-in-use') {
      errorMessage = 'El email de esta cuenta de Google ya está en uso';
    }

    return {
      success: false,
      error: errorMessage
    };
  }
}

/**
 * Vincula la cuenta actual con un número de teléfono
 * @param confirmationResult Resultado de la confirmación del código SMS
 * @param verificationCode Código de verificación recibido por SMS
 */
export async function linkAccountWithPhone(
  confirmationResult: ConfirmationResult,
  verificationCode: string
) {
  try {
    const auth = getFirebaseAuth();
    const currentUser = auth.currentUser;

    if (!currentUser) {
      return {
        success: false,
        error: 'No hay usuario autenticado para vincular'
      };
    }

    // Confirmar el código y obtener la credencial
    const result = await confirmationResult.confirm(verificationCode);

    return {
      success: true,
      user: {
        uid: result.user.uid,
        email: result.user.email,
        displayName: result.user.displayName,
        phoneNumber: result.user.phoneNumber
      }
    };
  } catch (error: any) {
    console.error('Error al vincular con teléfono:', error);

    // Manejar errores específicos
    let errorMessage = error.message || 'Error al vincular número de teléfono';
    if (error.code === 'auth/invalid-verification-code') {
      errorMessage = 'Código de verificación inválido';
    } else if (error.code === 'auth/credential-already-in-use') {
      errorMessage = 'Este número de teléfono ya está vinculado a otro usuario';
    } else if (error.code === 'auth/provider-already-linked') {
      errorMessage = 'Esta cuenta ya tiene un número de teléfono vinculado';
    }

    return {
      success: false,
      error: errorMessage
    };
  }
}

/**
 * Inicia el proceso de vinculación con teléfono enviando el código SMS
 * @param phoneNumber Número de teléfono con código de país (ej: +51999999999)
 * @param recaptchaVerifier Verificador de reCAPTCHA
 */
export async function startPhoneLinking(
  phoneNumber: string,
  recaptchaVerifier: RecaptchaVerifier
): Promise<{ success: boolean; confirmationResult?: ConfirmationResult; error?: string }> {
  try {
    const auth = getFirebaseAuth();
    const currentUser = auth.currentUser;

    if (!currentUser) {
      return {
        success: false,
        error: 'No hay usuario autenticado para vincular'
      };
    }

    // Enviar código de verificación usando la API moderna de Firebase Auth 2025
    const confirmationResult = await signInWithPhoneNumber(auth, phoneNumber, recaptchaVerifier);

    return {
      success: true,
      confirmationResult
    };
  } catch (error: any) {
    console.error('Error al iniciar vinculación con teléfono:', error);
    return {
      success: false,
      error: error.message || 'Error al enviar código de verificación'
    };
  }
}

/**
 * Obtiene la lista de proveedores de autenticación vinculados al usuario actual
 * @returns Array con información de proveedores vinculados (Google, phone, password, etc.)
 */
export function getLinkedProviders(): {
  success: boolean;
  providers?: Array<{
    providerId: string;
    displayName: string;
    email?: string;
    phoneNumber?: string;
  }>;
  error?: string
} {
  try {
    const auth = getFirebaseAuth();
    const currentUser = auth.currentUser;

    if (!currentUser) {
      return {
        success: false,
        error: 'No hay usuario autenticado'
      };
    }

    // Obtener información de los proveedores vinculados
    const providers = currentUser.providerData.map((profile) => {
      let displayName = 'Desconocido';

      // Asignar nombres legibles a cada proveedor
      switch (profile.providerId) {
        case 'google.com':
          displayName = 'Google';
          break;
        case 'phone':
          displayName = 'Teléfono';
          break;
        case 'password':
          displayName = 'Email/Contraseña';
          break;
        case 'facebook.com':
          displayName = 'Facebook';
          break;
        case 'apple.com':
          displayName = 'Apple';
          break;
        default:
          displayName = profile.providerId;
      }

      return {
        providerId: profile.providerId,
        displayName,
        email: profile.email || undefined,
        phoneNumber: profile.phoneNumber || undefined
      };
    });

    return {
      success: true,
      providers
    };
  } catch (error: any) {
    console.error('Error al obtener proveedores vinculados:', error);
    return {
      success: false,
      error: error.message || 'Error al obtener proveedores vinculados'
    };
  }
}

/**
 * Desvincula un proveedor de autenticación de la cuenta actual
 * @param providerId ID del proveedor a desvincular (ej: 'google.com', 'phone')
 */
export async function unlinkProvider(providerId: string): Promise<{ success: boolean; error?: string }> {
  try {
    const auth = getFirebaseAuth();
    const currentUser = auth.currentUser;

    if (!currentUser) {
      return {
        success: false,
        error: 'No hay usuario autenticado'
      };
    }

    // Verificar que el usuario tenga al menos 2 proveedores antes de desvincular
    const linkedProviders = currentUser.providerData;
    if (linkedProviders.length <= 1) {
      return {
        success: false,
        error: 'No puedes desvincular el único método de autenticación. Vincula otro método primero.'
      };
    }

    // Desvincular el proveedor usando la API de Firebase Auth
    const { unlink } = await import('firebase/auth');
    await unlink(currentUser, providerId);

    return {
      success: true
    };
  } catch (error: any) {
    console.error('Error al desvincular proveedor:', error);

    // Manejar errores específicos
    let errorMessage = error.message || 'Error al desvincular proveedor';
    if (error.code === 'auth/no-such-provider') {
      errorMessage = 'Este proveedor no está vinculado a tu cuenta';
    }

    return {
      success: false,
      error: errorMessage
    };
  }
}
