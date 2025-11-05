
"use client";

import { useState, useEffect, useRef } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import type { Message } from '@/lib/placeholder-data';
import { Send } from 'lucide-react';
import { cn } from '@/lib/utils';
import { useAppData } from '@/hooks/use-app-data';

interface ChatInterfaceProps {
  orderId: string;
  userType: 'customer' | 'driver';
  userName: string;
  receiverName: string;
}

export function ChatInterface({ orderId, userType, userName, receiverName }: ChatInterfaceProps) {
  const { getMessagesForOrder, addMessage } = useAppData();
  const [messages, setMessages] = useState<Message[]>([]);
  const [newMessage, setNewMessage] = useState('');
  const scrollAreaRef = useRef<HTMLDivElement>(null);

  const fetchMessages = () => {
    const orderMessages = getMessagesForOrder(orderId);
    setMessages(orderMessages);
  };

  useEffect(() => {
    fetchMessages();
    const interval = setInterval(fetchMessages, 2000); // Poll for new messages every 2 seconds

    return () => clearInterval(interval);
  }, [orderId, getMessagesForOrder]);

  useEffect(() => {
    // Scroll to bottom when messages change
    if (scrollAreaRef.current) {
        const viewport = scrollAreaRef.current.querySelector('div');
        if (viewport) {
            viewport.scrollTop = viewport.scrollHeight;
        }
    }
  }, [messages]);

  const handleSendMessage = (e: React.FormEvent) => {
    e.preventDefault();
    if (newMessage.trim() === '') return;

    addMessage({
      orderId,
      senderType: userType,
      senderName: userName,
      text: newMessage,
    });

    setNewMessage('');
    fetchMessages(); // Immediately fetch messages to show the new one
  };
  
  const getInitials = (name: string) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase();
  }

  return (
    <div className="flex flex-col h-full">
      <ScrollArea className="flex-grow p-4" ref={scrollAreaRef}>
        <div className="space-y-4">
          {messages.map((msg) => (
            <div
              key={msg.id}
              className={cn(
                'flex items-end gap-2',
                msg.senderType === userType ? 'justify-end' : 'justify-start'
              )}
            >
              {msg.senderType !== userType && (
                 <Avatar className="h-8 w-8">
                    <AvatarFallback>{getInitials(msg.senderName)}</AvatarFallback>
                </Avatar>
              )}
              <div
                className={cn(
                  'max-w-xs md:max-w-md rounded-lg px-4 py-2',
                  msg.senderType === userType
                    ? 'bg-primary text-primary-foreground'
                    : 'bg-muted'
                )}
              >
                <p className="text-sm">{msg.text}</p>
                <p className={cn("text-xs mt-1", msg.senderType === userType ? 'text-primary-foreground/70' : 'text-muted-foreground')}>
                  {new Date(msg.timestamp).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                </p>
              </div>
               {msg.senderType === userType && (
                 <Avatar className="h-8 w-8">
                    <AvatarFallback>{getInitials(msg.senderName)}</AvatarFallback>
                </Avatar>
              )}
            </div>
          ))}
        </div>
      </ScrollArea>
      <div className="border-t p-4">
        <form onSubmit={handleSendMessage} className="flex items-center gap-2">
          <Input
            value={newMessage}
            onChange={(e) => setNewMessage(e.target.value)}
            placeholder={`Chatear con ${receiverName}...`}
            autoComplete="off"
          />
          <Button type="submit" size="icon">
            <Send className="h-4 w-4" />
          </Button>
        </form>
      </div>
    </div>
  );
}
