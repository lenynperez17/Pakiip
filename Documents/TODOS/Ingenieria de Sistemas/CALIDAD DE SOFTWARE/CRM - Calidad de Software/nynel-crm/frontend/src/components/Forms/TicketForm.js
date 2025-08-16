import React, { useState, useEffect } from 'react';
import {
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Button,
  TextField,
  Grid,
  FormControl,
  InputLabel,
  Select,
  MenuItem,
  Box,
  Typography,
  Chip
} from '@mui/material';
import { ticketsAPI, contactsAPI } from '../../services/api';

const priorityOptions = [
  { value: 'low', label: 'Baja', color: '#4CAF50' },
  { value: 'medium', label: 'Media', color: '#FF9800' },
  { value: 'high', label: 'Alta', color: '#F44336' },
  { value: 'urgent', label: 'Urgente', color: '#9C27B0' }
];

const typeOptions = [
  { value: 'question', label: 'Pregunta' },
  { value: 'problem', label: 'Problema' },
  { value: 'feature_request', label: 'Solicitud de Funcionalidad' },
  { value: 'bug', label: 'Error/Bug' },
  { value: 'complaint', label: 'Queja' },
  { value: 'other', label: 'Otro' }
];

const statusOptions = [
  { value: 'new', label: 'Nuevo' },
  { value: 'open', label: 'Abierto' },
  { value: 'pending', label: 'Pendiente' },
  { value: 'on_hold', label: 'En Espera' },
  { value: 'resolved', label: 'Resuelto' },
  { value: 'closed', label: 'Cerrado' }
];

function TicketForm({ open, onClose, ticket = null, onSave }) {
  const [formData, setFormData] = useState({
    subject: '',
    description: '',
    priority: 'medium',
    ticket_type: 'question',
    status: 'new',
    contact: '',
    account: '',
    assigned_to: '',
    due_date: '',
    escalation_date: '',
    product_affected: '',
    resolution: ''
  });
  
  const [accounts, setAccounts] = useState([]);
  const [contacts, setContacts] = useState([]);
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(false);
  const [errors, setErrors] = useState({});

  useEffect(() => {
    if (open) {
      loadAccounts();
      loadUsers();
      if (ticket) {
        setFormData({
          subject: ticket.subject || '',
          description: ticket.description || '',
          priority: ticket.priority || 'medium',
          ticket_type: ticket.ticket_type || 'question',
          status: ticket.status || 'new',
          contact: ticket.contact || '',
          account: ticket.account || '',
          assigned_to: ticket.assigned_to || '',
          due_date: ticket.due_date || '',
          escalation_date: ticket.escalation_date || '',
          product_affected: ticket.product_affected || '',
          resolution: ticket.resolution || ''
        });
        if (ticket.account) {
          loadContacts(ticket.account);
        }
      } else {
        setFormData({
          subject: '',
          description: '',
          priority: 'medium',
          ticket_type: 'question',
          status: 'new',
          contact: '',
          account: '',
          assigned_to: '',
          due_date: '',
          escalation_date: '',
          product_affected: '',
          resolution: ''
        });
        setContacts([]);
      }
      setErrors({});
    }
  }, [open, ticket]);

  const loadAccounts = async () => {
    try {
      const response = await contactsAPI.getAccounts();
      setAccounts(response.data.results || response.data);
    } catch (error) {
      console.error('Error loading accounts:', error);
    }
  };

  const loadContacts = async (accountId) => {
    try {
      const response = await contactsAPI.getContacts({ account: accountId });
      setContacts(response.data.results || response.data);
    } catch (error) {
      console.error('Error loading contacts:', error);
    }
  };

  const loadUsers = async () => {
    try {
      // This would be a call to get support users
      setUsers([
        { id: 1, username: 'soporte1', first_name: 'Juan', last_name: 'Pérez' },
        { id: 2, username: 'admin', first_name: 'Admin', last_name: 'Sistema' }
      ]);
    } catch (error) {
      console.error('Error loading users:', error);
    }
  };

  const handleChange = (field) => (event) => {
    const value = event.target.value;
    setFormData(prev => ({
      ...prev,
      [field]: value
    }));
    
    if (field === 'account') {
      setFormData(prev => ({ ...prev, contact: '' }));
      if (value) {
        loadContacts(value);
      } else {
        setContacts([]);
      }
    }
    
    if (field === 'priority') {
      // Auto-set due date based on priority
      const now = new Date();
      let dueDate = new Date(now);
      
      switch (value) {
        case 'urgent':
          dueDate.setHours(now.getHours() + 2);
          break;
        case 'high':
          dueDate.setHours(now.getHours() + 8);
          break;
        case 'medium':
          dueDate.setDate(now.getDate() + 1);
          break;
        case 'low':
          dueDate.setDate(now.getDate() + 3);
          break;
        default:
          dueDate.setDate(now.getDate() + 1);
      }
      
      setFormData(prev => ({
        ...prev,
        due_date: dueDate.toISOString().split('T')[0]
      }));
    }
    
    if (errors[field]) {
      setErrors(prev => ({
        ...prev,
        [field]: ''
      }));
    }
  };

  const validateForm = () => {
    const newErrors = {};
    
    if (!formData.subject.trim()) {
      newErrors.subject = 'El asunto es requerido';
    }
    
    if (!formData.description.trim()) {
      newErrors.description = 'La descripción es requerida';
    }
    
    if (!formData.contact) {
      newErrors.contact = 'El contacto es requerido';
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async () => {
    if (!validateForm()) return;
    
    try {
      setLoading(true);
      if (ticket) {
        await ticketsAPI.updateTicket(ticket.id, formData);
      } else {
        await ticketsAPI.createTicket(formData);
      }
      onSave();
      onClose();
    } catch (error) {
      console.error('Error saving ticket:', error);
      if (error.response?.data) {
        setErrors(error.response.data);
      }
    } finally {
      setLoading(false);
    }
  };

  const getPriorityChip = (priority) => {
    const option = priorityOptions.find(p => p.value === priority);
    return (
      <Chip
        label={option?.label || priority}
        size="small"
        sx={{
          backgroundColor: option?.color || '#666',
          color: 'white'
        }}
      />
    );
  };

  return (
    <Dialog open={open} onClose={onClose} maxWidth="md" fullWidth>
      <DialogTitle>
        {ticket ? 'Editar Ticket' : 'Nuevo Ticket'}
      </DialogTitle>
      
      <DialogContent>
        <Box sx={{ pt: 1 }}>
          <Grid container spacing={2}>
            <Grid item xs={12}>
              <TextField
                fullWidth
                label="Asunto *"
                value={formData.subject}
                onChange={handleChange('subject')}
                error={!!errors.subject}
                helperText={errors.subject}
              />
            </Grid>
            
            <Grid item xs={12}>
              <TextField
                fullWidth
                label="Descripción *"
                value={formData.description}
                onChange={handleChange('description')}
                error={!!errors.description}
                helperText={errors.description}
                multiline
                rows={4}
              />
            </Grid>
            
            <Grid item xs={12} sm={6}>
              <FormControl fullWidth>
                <InputLabel>Cuenta</InputLabel>
                <Select
                  value={formData.account}
                  onChange={handleChange('account')}
                  label="Cuenta"
                >
                  {accounts.map((account) => (
                    <MenuItem key={account.id} value={account.id}>
                      {account.name}
                    </MenuItem>
                  ))}
                </Select>
              </FormControl>
            </Grid>
            
            <Grid item xs={12} sm={6}>
              <FormControl fullWidth error={!!errors.contact}>
                <InputLabel>Contacto *</InputLabel>
                <Select
                  value={formData.contact}
                  onChange={handleChange('contact')}
                  label="Contacto *"
                  disabled={!formData.account}
                >
                  {contacts.map((contact) => (
                    <MenuItem key={contact.id} value={contact.id}>
                      {contact.first_name} {contact.last_name}
                    </MenuItem>
                  ))}
                </Select>
                {errors.contact && (
                  <Typography variant="caption" color="error" sx={{ ml: 2 }}>
                    {errors.contact}
                  </Typography>
                )}
              </FormControl>
            </Grid>
            
            <Grid item xs={12} sm={6}>
              <FormControl fullWidth>
                <InputLabel>Tipo</InputLabel>
                <Select
                  value={formData.ticket_type}
                  onChange={handleChange('ticket_type')}
                  label="Tipo"
                >
                  {typeOptions.map((option) => (
                    <MenuItem key={option.value} value={option.value}>
                      {option.label}
                    </MenuItem>
                  ))}
                </Select>
              </FormControl>
            </Grid>
            
            <Grid item xs={12} sm={6}>
              <FormControl fullWidth>
                <InputLabel>Prioridad</InputLabel>
                <Select
                  value={formData.priority}
                  onChange={handleChange('priority')}
                  label="Prioridad"
                  renderValue={(value) => getPriorityChip(value)}
                >
                  {priorityOptions.map((option) => (
                    <MenuItem key={option.value} value={option.value}>
                      <Box display="flex" alignItems="center" gap={1}>
                        <Chip
                          label={option.label}
                          size="small"
                          sx={{
                            backgroundColor: option.color,
                            color: 'white'
                          }}
                        />
                      </Box>
                    </MenuItem>
                  ))}
                </Select>
              </FormControl>
            </Grid>
            
            <Grid item xs={12} sm={6}>
              <FormControl fullWidth>
                <InputLabel>Estado</InputLabel>
                <Select
                  value={formData.status}
                  onChange={handleChange('status')}
                  label="Estado"
                >
                  {statusOptions.map((option) => (
                    <MenuItem key={option.value} value={option.value}>
                      {option.label}
                    </MenuItem>
                  ))}
                </Select>
              </FormControl>
            </Grid>
            
            <Grid item xs={12} sm={6}>
              <FormControl fullWidth>
                <InputLabel>Asignado a</InputLabel>
                <Select
                  value={formData.assigned_to}
                  onChange={handleChange('assigned_to')}
                  label="Asignado a"
                >
                  {users.map((user) => (
                    <MenuItem key={user.id} value={user.id}>
                      {user.first_name} {user.last_name}
                    </MenuItem>
                  ))}
                </Select>
              </FormControl>
            </Grid>
            
            <Grid item xs={12} sm={6}>
              <TextField
                fullWidth
                label="Fecha Límite"
                type="datetime-local"
                value={formData.due_date}
                onChange={handleChange('due_date')}
                InputLabelProps={{ shrink: true }}
              />
            </Grid>
            
            <Grid item xs={12} sm={6}>
              <TextField
                fullWidth
                label="Fecha de Escalación"
                type="datetime-local"
                value={formData.escalation_date}
                onChange={handleChange('escalation_date')}
                InputLabelProps={{ shrink: true }}
              />
            </Grid>
            
            <Grid item xs={12}>
              <TextField
                fullWidth
                label="Producto Afectado"
                value={formData.product_affected}
                onChange={handleChange('product_affected')}
              />
            </Grid>
            
            {ticket && formData.status === 'resolved' && (
              <Grid item xs={12}>
                <TextField
                  fullWidth
                  label="Resolución"
                  value={formData.resolution}
                  onChange={handleChange('resolution')}
                  multiline
                  rows={3}
                />
              </Grid>
            )}
          </Grid>
        </Box>
      </DialogContent>
      
      <DialogActions>
        <Button onClick={onClose}>
          Cancelar
        </Button>
        <Button 
          onClick={handleSubmit}
          variant="contained"
          disabled={loading}
        >
          {loading ? 'Guardando...' : ticket ? 'Actualizar' : 'Crear'}
        </Button>
      </DialogActions>
    </Dialog>
  );
}

export default TicketForm;