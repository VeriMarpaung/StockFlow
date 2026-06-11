'use client';

import AppLayout from '@/components/AppLayout';
import api from '@/lib/api';
import Link from 'next/link';
import { useEffect, useState } from 'react';

type Summary = {
  total_products: number;
  low_stock_count: number;
  total_categories: number;
  transactions_today: number;
  unread_notifications: number;
};

type Notification = {
  id: number;
  title: string;
  message: string;
  read_at: string | null;
  created_at: string;
};

export default function DashboardPage() {
  const [summary, setSummary]           = useState<Summary | null>(null);
  const [notifications, setNotifications] = useState<Notification[]>([]);

  useEffect(() => {
    api.get('/dashboard/summary').then((r) => setSummary(r.data));
    api.get('/notifications').then((r) => setNotifications(r.data.data.slice(0, 5)));
  }, []);

  const cards = summary
    ? [
        { label: 'Total Products', value: summary.total_products, color: 'bg-blue-100 text-blue-800' },
        { label: 'Low Stock', value: summary.low_stock_count, color: 'bg-red-100 text-red-800' },
        { label: 'Categories', value: summary.total_categories, color: 'bg-green-100 text-green-800' },
        { label: 'Transactions Today', value: summary.transactions_today, color: 'bg-yellow-100 text-yellow-800' },
      ]
    : [];

  return (
    <AppLayout>
      <h1 className="text-xl font-bold text-gray-800 mb-6">Dashboard</h1>

      {/* Summary cards */}
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        {cards.map((c) => (
          <div key={c.label} className={`rounded-lg p-4 ${c.color}`}>
            <p className="text-sm font-medium">{c.label}</p>
            <p className="text-3xl font-bold mt-1">{c.value}</p>
          </div>
        ))}
      </div>

      {/* Recent notifications */}
      <div className="bg-white rounded-lg shadow-sm p-5">
        <div className="flex items-center justify-between mb-3">
          <h2 className="font-semibold text-gray-700">Recent Notifications</h2>
          <Link href="/notifications" className="text-sm text-blue-600 hover:underline">
            View all
          </Link>
        </div>
        {notifications.length === 0 ? (
          <p className="text-sm text-gray-400">No notifications</p>
        ) : (
          <ul className="space-y-2">
            {notifications.map((n) => (
              <li
                key={n.id}
                className={`text-sm px-3 py-2 rounded ${
                  n.read_at ? 'text-gray-500' : 'bg-blue-50 text-gray-800 font-medium'
                }`}
              >
                <p>{n.title}</p>
                <p className="text-xs text-gray-400 mt-0.5">{new Date(n.created_at).toLocaleString()}</p>
              </li>
            ))}
          </ul>
        )}
      </div>
    </AppLayout>
  );
}
