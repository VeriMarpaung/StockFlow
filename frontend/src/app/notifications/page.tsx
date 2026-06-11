'use client';

import AppLayout from '@/components/AppLayout';
import api from '@/lib/api';
import { useEffect, useState } from 'react';

type Notification = {
  id: number;
  type: string;
  title: string;
  message: string;
  read_at: string | null;
  created_at: string;
};

export default function NotificationsPage() {
  const [notifications, setNotifications] = useState<Notification[]>([]);

  const load = () => api.get('/notifications').then((r) => setNotifications(r.data.data));

  useEffect(() => { load(); }, []);

  const markRead = async (id: number) => {
    await api.patch(`/notifications/${id}/read`);
    load();
  };

  const unreadCount = notifications.filter((n) => !n.read_at).length;

  return (
    <AppLayout>
      <div className="flex items-center gap-3 mb-6">
        <h1 className="text-xl font-bold text-gray-800">Notifications</h1>
        {unreadCount > 0 && (
          <span className="bg-red-500 text-white text-xs px-2 py-0.5 rounded-full font-medium">
            {unreadCount} unread
          </span>
        )}
      </div>

      <div className="space-y-3 max-w-2xl">
        {notifications.length === 0 && (
          <p className="text-gray-400 text-sm">No notifications</p>
        )}
        {notifications.map((n) => (
          <div
            key={n.id}
            className={`bg-white rounded-lg shadow-sm p-4 border-l-4 ${
              n.read_at ? 'border-gray-200 opacity-70' : 'border-blue-500'
            }`}
          >
            <div className="flex items-start justify-between">
              <div>
                <p className={`text-sm font-medium ${n.read_at ? 'text-gray-500' : 'text-gray-800'}`}>
                  {n.title}
                </p>
                <p className="text-sm text-gray-500 mt-0.5">{n.message}</p>
                <p className="text-xs text-gray-400 mt-1">{new Date(n.created_at).toLocaleString()}</p>
              </div>
              {!n.read_at && (
                <button
                  onClick={() => markRead(n.id)}
                  className="text-xs text-blue-600 hover:underline ml-4 shrink-0"
                >
                  Mark read
                </button>
              )}
            </div>
          </div>
        ))}
      </div>
    </AppLayout>
  );
}
