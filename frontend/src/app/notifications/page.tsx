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

      <div className="space-y-2.5 max-w-2xl">
        {notifications.length === 0 && (
          <div className="bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-8 text-center">
            <div className="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-2">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="w-5 h-5 text-gray-400">
                <path strokeLinecap="round" strokeLinejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
              </svg>
            </div>
            <p className="text-gray-400 text-sm">No notifications yet</p>
          </div>
        )}
        {notifications.map((n) => (
          <div
            key={n.id}
            className={`bg-white rounded-xl shadow-sm p-4 border-l-4 transition-shadow hover:shadow-md ${
              n.read_at ? 'border-gray-200' : 'border-blue-500'
            }`}
          >
            <div className="flex items-start justify-between gap-4">
              <div className="flex items-start gap-3 min-w-0">
                <div className={`w-2 h-2 rounded-full mt-1.5 shrink-0 ${n.read_at ? 'bg-gray-300' : 'bg-blue-500'}`} />
                <div className="min-w-0">
                  <p className={`text-sm ${n.read_at ? 'text-gray-500' : 'font-semibold text-gray-800'}`}>
                    {n.title}
                  </p>
                  <p className="text-sm text-gray-500 mt-0.5">{n.message}</p>
                  <p className="text-xs text-gray-400 mt-1">{new Date(n.created_at).toLocaleString()}</p>
                </div>
              </div>
              {!n.read_at && (
                <button
                  onClick={() => markRead(n.id)}
                  className="text-xs text-blue-600 hover:text-blue-800 font-medium shrink-0 bg-blue-50 hover:bg-blue-100 px-2.5 py-1 rounded-full transition-colors"
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
