// Funciones de Firestore para el sistema de monedas Pakiip
import {
  getFirestore,
  doc,
  getDoc,
  setDoc,
  updateDoc,
  collection,
  getDocs,
  addDoc,
  query,
  where,
  orderBy,
  serverTimestamp
} from 'firebase/firestore';
import { getApp } from 'firebase/app';
import type { CoinTransaction, Reward } from '@/lib/placeholder-data';

/**
 * Obtiene la instancia de Firestore
 */
function getFirestoreInstance() {
  try {
    const app = getApp();
    return getFirestore(app);
  } catch (error) {
    console.error("Firebase no está inicializado:", error);
    throw new Error("Firebase no está configurado. Por favor, verifica tu configuración.");
  }
}

/**
 * Guarda el balance de monedas del usuario en Firestore
 */
export async function saveUserCoinsToFirestore(userId: string, coins: number): Promise<void> {
  const db = getFirestoreInstance();
  const userCoinsRef = doc(db, 'userCoins', userId);

  try {
    await setDoc(userCoinsRef, {
      userId,
      coins,
      updatedAt: serverTimestamp()
    }, { merge: true });

    console.log(`✅ Monedas guardadas en Firestore para usuario ${userId}: ${coins}`);
  } catch (error) {
    console.error("Error guardando monedas en Firestore:", error);
    throw error;
  }
}

/**
 * Obtiene el balance de monedas y transacciones del usuario desde Firestore
 */
export async function getUserCoinsFromFirestore(userId: string): Promise<{ coins: number; transactions: CoinTransaction[] } | null> {
  const db = getFirestoreInstance();

  try {
    // Obtener balance
    const userCoinsRef = doc(db, 'userCoins', userId);
    const userCoinsSnap = await getDoc(userCoinsRef);

    let coins = 0;
    if (userCoinsSnap.exists()) {
      coins = userCoinsSnap.data().coins || 0;
    }

    // Obtener transacciones
    const transactionsRef = collection(db, 'coinTransactions');
    const q = query(
      transactionsRef,
      where('userId', '==', userId)
    );

    const transactionsSnap = await getDocs(q);
    const transactions: CoinTransaction[] = [];

    transactionsSnap.forEach((doc) => {
      transactions.push({
        id: doc.id,
        ...doc.data()
      } as CoinTransaction);
    });

    // Ordenar transacciones en memoria (sin índice de Firestore)
    transactions.sort((a, b) => {
      const dateA = new Date(a.timestamp).getTime();
      const dateB = new Date(b.timestamp).getTime();
      return dateB - dateA; // Ordenar de más reciente a más antiguo
    });

    console.log(`✅ Datos de monedas cargados desde Firestore para usuario ${userId}`);

    return { coins, transactions };
  } catch (error) {
    console.error("Error obteniendo datos de monedas desde Firestore:", error);
    throw error;
  }
}

/**
 * Agrega una nueva transacción de monedas en Firestore
 */
export async function addCoinTransactionToFirestore(
  userId: string,
  transaction: Omit<CoinTransaction, 'id'>
): Promise<string> {
  const db = getFirestoreInstance();
  const transactionsRef = collection(db, 'coinTransactions');

  try {
    const docRef = await addDoc(transactionsRef, {
      ...transaction,
      timestamp: serverTimestamp()
    });

    console.log(`✅ Transacción de monedas guardada en Firestore: ${docRef.id}`);
    return docRef.id;
  } catch (error) {
    console.error("Error guardando transacción en Firestore:", error);
    throw error;
  }
}

/**
 * Otorga monedas a un usuario por completar un pedido
 */
export async function awardCoinsForOrder(
  userId: string,
  orderId: string,
  orderTotal: number
): Promise<void> {
  // Calcular monedas: 10 monedas por cada S/ 10 gastados
  const coinsEarned = Math.floor(orderTotal / 10) * 10;

  if (coinsEarned <= 0) return;

  try {
    // Obtener balance actual
    const userData = await getUserCoinsFromFirestore(userId);
    const currentCoins = userData?.coins || 0;
    const newBalance = currentCoins + coinsEarned;

    // Crear transacción
    const transaction: Omit<CoinTransaction, 'id'> = {
      userId,
      type: 'earned',
      amount: coinsEarned,
      reason: 'order_completed',
      description: `Ganaste ${coinsEarned} monedas por tu pedido`,
      timestamp: new Date().toISOString(),
      relatedOrderId: orderId
    };

    // Guardar transacción y nuevo balance
    await addCoinTransactionToFirestore(userId, transaction);
    await saveUserCoinsToFirestore(userId, newBalance);

    console.log(`✅ ${coinsEarned} monedas otorgadas al usuario ${userId} por pedido ${orderId}`);
  } catch (error) {
    console.error("Error otorgando monedas:", error);
    throw error;
  }
}

/**
 * Obtiene todas las recompensas disponibles desde Firestore
 */
export async function getRewardsFromFirestore(): Promise<Reward[]> {
  const db = getFirestoreInstance();
  const rewardsRef = collection(db, 'rewards');

  try {
    const q = query(rewardsRef, where('isActive', '==', true));
    const rewardsSnap = await getDocs(q);

    const rewards: Reward[] = [];
    rewardsSnap.forEach((doc) => {
      rewards.push({
        id: doc.id,
        ...doc.data()
      } as Reward);
    });

    console.log(`✅ ${rewards.length} recompensas cargadas desde Firestore`);
    return rewards;
  } catch (error) {
    console.error("Error obteniendo recompensas desde Firestore:", error);
    // Retornar recompensas de ejemplo si falla
    return getDefaultRewards();
  }
}

/**
 * Crea recompensas de ejemplo en Firestore (solo para inicialización)
 */
export async function createDefaultRewardsInFirestore(): Promise<void> {
  const db = getFirestoreInstance();
  const rewards = getDefaultRewards();

  try {
    for (const reward of rewards) {
      const rewardRef = doc(db, 'rewards', reward.id);
      await setDoc(rewardRef, reward);
    }
    console.log("✅ Recompensas de ejemplo creadas en Firestore");
  } catch (error) {
    console.error("Error creando recompensas de ejemplo:", error);
  }
}

/**
 * Recompensas de ejemplo
 */
function getDefaultRewards(): Reward[] {
  return [
    {
      id: 'reward_1',
      name: '10% de descuento',
      description: 'Obtén 10% de descuento en tu próximo pedido',
      coinsCost: 100,
      imageUrl: '/rewards/discount-10.png',
      type: 'discount',
      value: 10,
      stock: 50,
      isActive: true
    },
    {
      id: 'reward_2',
      name: 'Envío gratis',
      description: 'Delivery gratis en tu próxima orden',
      coinsCost: 150,
      imageUrl: '/rewards/free-delivery.png',
      type: 'freeDelivery',
      value: 0,
      stock: 30,
      isActive: true
    },
    {
      id: 'reward_3',
      name: 'Vale de S/ 20',
      description: 'Vale de descuento de S/ 20 para tu próxima compra',
      coinsCost: 200,
      imageUrl: '/rewards/voucher-20.png',
      type: 'voucher',
      value: 20,
      stock: 20,
      isActive: true
    },
    {
      id: 'reward_4',
      name: '20% de descuento',
      description: 'Obtén 20% de descuento en tu próximo pedido',
      coinsCost: 250,
      imageUrl: '/rewards/discount-20.png',
      type: 'discount',
      value: 20,
      stock: 15,
      isActive: true
    },
    {
      id: 'reward_5',
      name: 'Vale de S/ 50',
      description: 'Vale de descuento de S/ 50 para tu próxima compra',
      coinsCost: 500,
      imageUrl: '/rewards/voucher-50.png',
      type: 'voucher',
      value: 50,
      stock: 10,
      isActive: true
    }
  ];
}

/**
 * Otorga monedas de bono a un usuario (para promociones especiales)
 */
export async function awardBonusCoins(
  userId: string,
  amount: number,
  reason: string,
  description: string
): Promise<void> {
  try {
    // Obtener balance actual
    const userData = await getUserCoinsFromFirestore(userId);
    const currentCoins = userData?.coins || 0;
    const newBalance = currentCoins + amount;

    // Crear transacción
    const transaction: Omit<CoinTransaction, 'id'> = {
      userId,
      type: 'bonus',
      amount,
      reason,
      description,
      timestamp: new Date().toISOString()
    };

    // Guardar transacción y nuevo balance
    await addCoinTransactionToFirestore(userId, transaction);
    await saveUserCoinsToFirestore(userId, newBalance);

    console.log(`✅ ${amount} monedas de bono otorgadas al usuario ${userId}`);
  } catch (error) {
    console.error("Error otorgando monedas de bono:", error);
    throw error;
  }
}
