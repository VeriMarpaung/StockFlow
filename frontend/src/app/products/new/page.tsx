'use client';

import AppLayout from '@/components/AppLayout';
import api, { isAxiosError } from '@/lib/api';
import { useRouter } from 'next/navigation';
import { useEffect, useState } from 'react';

type Category = { id: number; name: string };

export default function NewProductPage() {
  const router = useRouter();
  const [categories, setCategories] = useState<Category[]>([]);
  const [form, setForm] = useState({
    category_id: '',
    name: '',
    sku: '',
    description: '',
    price: '',
    stock: '0',
    threshold: '10',
  });
  const [error, setError]   = useState('');
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    api.get('/categories').then((r) => setCategories(r.data.data));
  }, []);

  const set = (field: string) => (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>) =>
    setForm((f) => ({ ...f, [field]: e.target.value }));

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setLoading(true);
    try {
      await api.post('/products', {
        ...form,
        category_id: Number(form.category_id),
        price: Number(form.price),
        stock: Number(form.stock),
        threshold: Number(form.threshold),
      });
      router.push('/products');
    } catch (err) {
      if (isAxiosError(err) && err.response?.status === 422) {
        const msgs = Object.values(err.response.data.errors ?? {}).flat();
        setError((msgs[0] as string) ?? 'Validation error');
      } else {
        setError('Terjadi kesalahan.');
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <AppLayout>
      <h1 className="text-xl font-bold text-gray-800 mb-6">New Product</h1>
      <div className="bg-white rounded-lg shadow-sm p-6 max-w-lg">
        <form onSubmit={handleSubmit} className="space-y-4">
          <Field label="Category">
            <select value={form.category_id} onChange={set('category_id')} required className={inputCls}>
              <option value="">— Select —</option>
              {categories.map((c) => (
                <option key={c.id} value={c.id}>{c.name}</option>
              ))}
            </select>
          </Field>
          <Field label="Name">
            <input value={form.name} onChange={set('name')} required className={inputCls} />
          </Field>
          <Field label="SKU">
            <input value={form.sku} onChange={set('sku')} required className={inputCls} />
          </Field>
          <Field label="Description">
            <textarea value={form.description} onChange={set('description')} rows={2} className={inputCls} />
          </Field>
          <div className="grid grid-cols-3 gap-3">
            <Field label="Price (Rp)">
              <input type="number" value={form.price} onChange={set('price')} required min="0" className={inputCls} />
            </Field>
            <Field label="Initial Stock">
              <input type="number" value={form.stock} onChange={set('stock')} min="0" className={inputCls} />
            </Field>
            <Field label="Threshold">
              <input type="number" value={form.threshold} onChange={set('threshold')} min="0" className={inputCls} />
            </Field>
          </div>
          {error && <p className="text-red-600 text-sm">{error}</p>}
          <div className="flex gap-3">
            <button type="submit" disabled={loading} className="bg-blue-700 text-white px-5 py-2 rounded text-sm font-medium hover:bg-blue-800 disabled:opacity-50">
              {loading ? 'Saving…' : 'Save'}
            </button>
            <button type="button" onClick={() => router.back()} className="px-5 py-2 rounded text-sm border border-gray-300 hover:bg-gray-50">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </AppLayout>
  );
}

const inputCls = 'w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500';

function Field({ label, children }: { label: string; children: React.ReactNode }) {
  return (
    <div>
      <label className="block text-sm font-medium text-gray-700 mb-1">{label}</label>
      {children}
    </div>
  );
}
