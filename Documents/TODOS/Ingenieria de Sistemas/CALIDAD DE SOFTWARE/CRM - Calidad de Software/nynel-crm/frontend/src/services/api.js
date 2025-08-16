import axios from 'axios';

// Create axios instance with base configuration
const api = axios.create({
  baseURL: process.env.REACT_APP_API_URL || 'http://localhost:8000/api/v1',
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
  },
});

// Request interceptor to add auth token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token'); // Fixed token name
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor to handle errors - DISABLED FOR DEMO
// api.interceptors.response.use(
//   (response) => {
//     return response;
//   },
//   (error) => {
//     if (error.response?.status === 401) {
//       // Handle unauthorized access
//       localStorage.removeItem('auth_token');
//       window.location.href = '/login';
//     }
//     return Promise.reject(error);
//   }
// );

// Authentication API endpoints
export const authAPI = {
  login: (email, password) => api.post('/auth/login/', { email, password }),
  logout: () => api.post('/auth/logout/'),
  refreshToken: (refresh) => api.post('/auth/token/refresh/', { refresh }),
  getUser: () => api.get('/auth/user/'),
  changePassword: (data) => api.post('/auth/change-password/', data),
  resetPasswordRequest: (email) => api.post('/auth/reset-password/', { email }),
  resetPasswordConfirm: (data) => api.post('/auth/reset-password/confirm/', data),
};

// Demo data for simulation
const demoContacts = [
  { id: 1, first_name: 'Luis', last_name: 'Rodríguez', email: 'luis.rodriguez@techsolutions.com.pe', phone: '+51987654321', job_title: 'Gerente General', account_name: 'Tech Solutions SAC' },
  { id: 2, first_name: 'Ana', last_name: 'Torres', email: 'ana.torres@innovatecorp.com.pe', phone: '+51876543210', job_title: 'Directora de TI', account_name: 'Innovate Corp EIRL' },
  { id: 3, first_name: 'Carlos', last_name: 'Mendoza', email: 'carlos.mendoza@gmail.com', phone: '+51912345678', job_title: 'Desarrollador', account_name: 'Tech Solutions SAC' },
];

const demoAccounts = [
  { id: 1, name: 'Tech Solutions SAC', ruc: '20123456789', account_type: 'customer', industry: 'Tecnología', contacts_count: 2 },
  { id: 2, name: 'Innovate Corp EIRL', ruc: '20987654321', account_type: 'prospect', industry: 'Consultoría', contacts_count: 1 },
];

// Simulated API delay
const delay = (ms) => new Promise(resolve => setTimeout(resolve, ms));

// API endpoints for different modules (DEMO MODE)
export const contactsAPI = {
  getContacts: async (params = {}) => {
    await delay(500);
    return { data: { results: demoContacts } };
  },
  getAccounts: async (params = {}) => {
    await delay(500);
    return { data: { results: demoAccounts } };
  },
  getContact: async (id) => {
    await delay(500);
    return { data: demoContacts.find(c => c.id === parseInt(id)) };
  },
  createContact: async (data) => {
    await delay(500);
    const newContact = { ...data, id: Date.now() };
    demoContacts.push(newContact);
    return { data: newContact };
  },
  updateContact: async (id, data) => {
    await delay(500);
    const index = demoContacts.findIndex(c => c.id === parseInt(id));
    if (index !== -1) {
      demoContacts[index] = { ...demoContacts[index], ...data };
    }
    return { data: demoContacts[index] };
  },
  deleteContact: async (id) => {
    await delay(500);
    const index = demoContacts.findIndex(c => c.id === parseInt(id));
    if (index !== -1) {
      demoContacts.splice(index, 1);
    }
    return { data: { success: true } };
  },
  getContactActivities: async (id) => {
    await delay(500);
    return { data: [] };
  },
  searchByEmail: async (email) => {
    await delay(500);
    return { data: demoContacts.filter(c => c.email.includes(email)) };
  },
  getStatistics: async () => {
    await delay(500);
    return { data: { total_contacts: demoContacts.length, total_accounts: demoAccounts.length } };
  },
  createAccount: async (data) => {
    await delay(500);
    const newAccount = { ...data, id: Date.now(), contacts_count: 0 };
    demoAccounts.push(newAccount);
    return { data: newAccount };
  },
  updateAccount: async (id, data) => {
    await delay(500);
    const index = demoAccounts.findIndex(a => a.id === parseInt(id));
    if (index !== -1) {
      demoAccounts[index] = { ...demoAccounts[index], ...data };
    }
    return { data: demoAccounts[index] };
  },
  deleteAccount: async (id) => {
    await delay(500);
    const index = demoAccounts.findIndex(a => a.id === parseInt(id));
    if (index !== -1) {
      demoAccounts.splice(index, 1);
    }
    return { data: { success: true } };
  },
};

// Demo data for other modules
const demoOpportunities = [
  { id: 1, name: 'Proyecto ERP Tech Solutions', stage: 'negotiation', amount: 85000, probability: 80, close_date: '2024-07-15', account_name: 'Tech Solutions SAC' },
  { id: 2, name: 'Software CRM Innovate', stage: 'proposal', amount: 65000, probability: 60, close_date: '2024-08-20', account_name: 'Innovate Corp EIRL' },
];

const demoTickets = [
  { id: 1, subject: 'Error en sistema de login', status: 'open', priority: 'high', contact_name: 'Luis Rodríguez', account_name: 'Tech Solutions SAC' },
  { id: 2, subject: 'Consulta sobre licencias', status: 'new', priority: 'medium', contact_name: 'Ana Torres', account_name: 'Innovate Corp EIRL' },
];

const demoCampaigns = [
  { id: 1, name: 'Campaña Email Verano 2024', status: 'active', campaign_type: 'email', budget: 5000, expected_leads: 100 },
  { id: 2, name: 'Webinar Productos Nuevos', status: 'planning', campaign_type: 'webinar', budget: 8000, expected_leads: 200 },
];

const demoLeads = [
  { id: 1, first_name: 'Roberto', last_name: 'Silva', email: 'roberto@empresa.com', company: 'Nueva Empresa SAC', status: 'new', score: 75, source: 'website' },
  { id: 2, first_name: 'María', last_name: 'López', email: 'maria@startup.com', company: 'Startup Innovadora', status: 'qualified', score: 85, source: 'referral' },
];

// Simulated APIs (DEMO MODE)
export const opportunitiesAPI = {
  getOpportunities: async (params = {}) => {
    await delay(500);
    return { data: { results: demoOpportunities } };
  },
  getPipelineAnalysis: async () => {
    await delay(500);
    return { data: {
      qualification: { count: 3, value: 50000 },
      needs_analysis: { count: 2, value: 75000 },
      proposal: { count: 1, value: 65000 },
      negotiation: { count: 1, value: 85000 },
      closed_won: { count: 0, value: 0 },
      closed_lost: { count: 0, value: 0 }
    }};
  },
  createOpportunity: async (data) => {
    await delay(500);
    const newOpp = { ...data, id: Date.now() };
    demoOpportunities.push(newOpp);
    return { data: newOpp };
  },
  updateOpportunity: async (id, data) => {
    await delay(500);
    const index = demoOpportunities.findIndex(o => o.id === parseInt(id));
    if (index !== -1) {
      demoOpportunities[index] = { ...demoOpportunities[index], ...data };
    }
    return { data: demoOpportunities[index] };
  },
  deleteOpportunity: async (id) => {
    await delay(500);
    return { data: { success: true } };
  },
};

export const ticketsAPI = {
  getTickets: async (params = {}) => {
    await delay(500);
    return { data: { results: demoTickets } };
  },
  getTicketStatistics: async () => {
    await delay(500);
    return { data: {
      open_tickets: 15,
      overdue_tickets: 3,
      avg_satisfaction: 4.2,
      avg_response_time: 2.5
    }};
  },
  createTicket: async (data) => {
    await delay(500);
    const newTicket = { ...data, id: Date.now(), ticket_number: `TK-${Date.now()}` };
    demoTickets.push(newTicket);
    return { data: newTicket };
  },
  updateTicket: async (id, data) => {
    await delay(500);
    return { data: { success: true } };
  },
  deleteTicket: async (id) => {
    await delay(500);
    return { data: { success: true } };
  },
  resolveTicket: async (id) => {
    await delay(500);
    return { data: { success: true } };
  },
};

export const marketingAPI = {
  getCampaigns: async (params = {}) => {
    await delay(500);
    return { data: { results: demoCampaigns } };
  },
  getLeads: async (params = {}) => {
    await delay(500);
    return { data: { results: demoLeads } };
  },
  getEmailTemplates: async (params = {}) => {
    await delay(500);
    return { data: { results: [
      { id: 1, name: 'Bienvenida Cliente', subject: 'Bienvenido a NYNEL CRM', is_active: true, times_used: 25, created_at: '2024-01-15' },
      { id: 2, name: 'Follow-up Ventas', subject: 'Seguimiento de propuesta', is_active: true, times_used: 15, created_at: '2024-02-20' }
    ]}};
  },
  createCampaign: async (data) => {
    await delay(500);
    const newCampaign = { ...data, id: Date.now() };
    demoCampaigns.push(newCampaign);
    return { data: newCampaign };
  },
  updateCampaign: async (id, data) => {
    await delay(500);
    return { data: { success: true } };
  },
  deleteCampaign: async (id) => {
    await delay(500);
    return { data: { success: true } };
  },
  deleteLead: async (id) => {
    await delay(500);
    return { data: { success: true } };
  },
  deleteEmailTemplate: async (id) => {
    await delay(500);
    return { data: { success: true } };
  },
};

export default api;