'use client';

import AppLayout from '@/components/AppLayout';
import api, { isAxiosError } from '@/lib/api';
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

type InsightData = {
  insights: string;
  generated_at: string;
  cached: boolean;
};

const cardIcons = [
  // Total Products
  <svg key="products" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="w-5 h-5">
    <path strokeLinecap="round" strokeLinejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
  </svg>,
  // Low Stock
  <svg key="warning" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="w-5 h-5">
    <path strokeLinecap="round" strokeLinejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
  </svg>,
  // Categories
  <svg key="tag" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="w-5 h-5">
    <path strokeLinecap="round" strokeLinejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
    <path strokeLinecap="round" strokeLinejoin="round" d="M6 6h.008v.008H6V6z" />
  </svg>,
  // Transactions
  <svg key="chart" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="w-5 h-5">
    <path strokeLinecap="round" strokeLinejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
  </svg>,
];

export default function DashboardPage() {
  const [summary, setSummary]               = useState<Summary | null>(null);
  const [notifications, setNotifications]   = useState<Notification[]>([]);
  const [insight, setInsight]               = useState<InsightData | null>(null);
  const [insightLoading, setInsightLoading] = useState(false);
  const [insightError, setInsightError]     = useState('');

  useEffect(() => {
    api.get('/dashboard/summary').then(({ data }: { data: Summary }) => setSummary(data));
    api.get('/notifications').then(({ data }: { data: { data: Notification[] } }) => setNotifications(data.data.slice(0, 5)));
    loadInsight();
  }, []);

  const loadInsight = () => {
    setInsightLoading(true);
    setInsightError('');
    api.get('/analytics/insights')
      .then(({ data }: { data: InsightData }) => setInsight(data))
      .catch((err: unknown) => {
        if (isAxiosError(err)) setInsightError('Gagal memuat insight AI.');
      })
      .finally(() => setInsightLoading(false));
  };

  const regenerate = async () => {
    setInsightLoading(true);
    setInsightError('');
    try {
      const r = await api.post<InsightData>('/analytics/insights/regenerate');
      setInsight(r.data);
    } catch {
      setInsightError('Gagal regenerate insight.');
    } finally {
      setInsightLoading(false);
    }
  };

  const cards = summary
    ? [
        { label: 'Total Products',     value: summary.total_products,     border: 'border-blue-500',  textColor: 'text-blue-600',   iconBg: 'bg-blue-50 text-blue-500' },
        { label: 'Low Stock',          value: summary.low_stock_count,    border: 'border-red-500',   textColor: 'text-red-600',    iconBg: 'bg-red-50 text-red-500' },
        { label: 'Categories',         value: summary.total_categories,   border: 'border-emerald-500', textColor: 'text-emerald-600', iconBg: 'bg-emerald-50 text-emerald-500' },
        { label: 'Transactions Today', value: summary.transactions_today, border: 'border-amber-500', textColor: 'text-amber-600',  iconBg: 'bg-amber-50 text-amber-500' },
      ]
    : [];

  return (
    <AppLayout>
      <h1 className="text-xl font-bold text-gray-800 mb-6">Dashboard</h1>

      {/* Summary cards */}
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        {cards.map((c, i) => (
          <div key={c.label} className={`bg-white rounded-xl shadow-sm p-5 border-t-4 ${c.border}`}>
            <div className="flex items-start justify-between">
              <div>
                <p className={`text-xs font-semibold uppercase tracking-wide ${c.textColor}`}>{c.label}</p>
                <p className="text-3xl font-bold text-gray-800 mt-1.5">{c.value}</p>
              </div>
              <div className={`w-10 h-10 rounded-xl flex items-center justify-center ${c.iconBg}`}>
                {cardIcons[i]}
              </div>
            </div>
          </div>
        ))}
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* AI Insight Panel */}
        <div className="bg-white rounded-xl shadow-sm overflow-hidden border border-purple-100">
          <div className="flex items-center justify-between px-5 py-3.5 bg-gradient-to-r from-purple-50 to-indigo-50 border-b border-purple-100">
            <div className="flex items-center gap-2">
              <span className="text-purple-600 text-lg leading-none">✦</span>
              <h2 className="font-semibold text-gray-700 text-sm">AI Inventory Insights</h2>
              {insight?.cached && (
                <span className="text-xs bg-white border border-purple-200 text-purple-500 px-2 py-0.5 rounded-full">cached</span>
              )}
            </div>
            <button
              onClick={regenerate}
              disabled={insightLoading}
              className="text-xs text-purple-600 hover:text-purple-800 font-medium disabled:opacity-40 transition-colors"
            >
              {insightLoading ? 'Loading…' : 'Regenerate'}
            </button>
          </div>

          <div className="p-5">
            {insightLoading && (
              <div className="flex items-center gap-2.5 text-sm text-gray-400 py-4">
                <div className="w-4 h-4 border-2 border-purple-200 border-t-purple-500 rounded-full animate-spin shrink-0" />
                Generating insight from AI…
              </div>
            )}

            {!insightLoading && insightError && (
              <p className="text-sm text-red-500">{insightError}</p>
            )}

            {!insightLoading && insight && (
              <>
                <p className="text-sm text-gray-700 whitespace-pre-line leading-relaxed">
                  {insight.insights}
                </p>
                <p className="text-xs text-gray-400 mt-3">
                  Generated: {new Date(insight.generated_at).toLocaleString()}
                </p>
              </>
            )}
          </div>
        </div>

        {/* Recent notifications */}
        <div className="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
          <div className="flex items-center justify-between px-5 py-3.5 bg-gray-50 border-b border-gray-100">
            <h2 className="font-semibold text-gray-700 text-sm">Recent Notifications</h2>
            <Link href="/notifications" className="text-xs text-blue-600 hover:text-blue-800 font-medium transition-colors">
              View all
            </Link>
          </div>

          <div className="p-4">
            {notifications.length === 0 ? (
              <p className="text-sm text-gray-400 py-2">No notifications</p>
            ) : (
              <ul className="space-y-1.5">
                {notifications.map((n) => (
                  <li
                    key={n.id}
                    className={`flex items-start gap-2.5 text-sm px-3 py-2.5 rounded-lg ${
                      n.read_at ? 'text-gray-500' : 'bg-blue-50 text-gray-800'
                    }`}
                  >
                    <div className={`w-2 h-2 rounded-full mt-1.5 shrink-0 ${n.read_at ? 'bg-gray-300' : 'bg-blue-500'}`} />
                    <div className="min-w-0">
                      <p className={n.read_at ? '' : 'font-medium'}>{n.title}</p>
                      <p className="text-xs text-gray-400 mt-0.5">{new Date(n.created_at).toLocaleString()}</p>
                    </div>
                  </li>
                ))}
              </ul>
            )}
          </div>
        </div>
      </div>
    </AppLayout>
  );
}
