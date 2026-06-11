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
        { label: 'Total Products',      value: summary.total_products,    color: 'bg-blue-100 text-blue-800' },
        { label: 'Low Stock',           value: summary.low_stock_count,   color: 'bg-red-100 text-red-800' },
        { label: 'Categories',          value: summary.total_categories,  color: 'bg-green-100 text-green-800' },
        { label: 'Transactions Today',  value: summary.transactions_today,color: 'bg-yellow-100 text-yellow-800' },
      ]
    : [];

  return (
    <AppLayout>
      <h1 className="text-xl font-bold text-gray-800 mb-6">Dashboard</h1>

      {/* Summary cards */}
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        {cards.map((c) => (
          <div key={c.label} className={`rounded-lg p-4 ${c.color}`}>
            <p className="text-sm font-medium">{c.label}</p>
            <p className="text-3xl font-bold mt-1">{c.value}</p>
          </div>
        ))}
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* AI Insight Panel */}
        <div className="bg-white rounded-lg shadow-sm p-5 border border-purple-100">
          <div className="flex items-center justify-between mb-3">
            <div className="flex items-center gap-2">
              <span className="text-purple-600 text-lg">✦</span>
              <h2 className="font-semibold text-gray-700">AI Inventory Insights</h2>
              {insight?.cached && (
                <span className="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">cached</span>
              )}
            </div>
            <button
              onClick={regenerate}
              disabled={insightLoading}
              className="text-xs text-purple-600 hover:underline disabled:opacity-40"
            >
              {insightLoading ? 'Loading…' : 'Regenerate'}
            </button>
          </div>

          {insightLoading && (
            <div className="flex items-center gap-2 text-sm text-gray-400 py-4">
              <div className="w-4 h-4 border-2 border-purple-300 border-t-purple-600 rounded-full animate-spin" />
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
      </div>
    </AppLayout>
  );
}
