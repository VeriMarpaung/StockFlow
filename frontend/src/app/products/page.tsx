'use client';

import AppLayout from '@/components/AppLayout';
import api from '@/lib/api';
import Link from 'next/link';
import { useEffect, useState } from 'react';

type Product = {
  id: number;
  name: string;
  sku: string;
  price: number;
  stock: number;
  threshold: number;
  version: number;
  category?: { id: number; name: string };
};

export default function ProductsPage() {
  const [products, setProducts] = useState<Product[]>([]);
  const [search, setSearch]     = useState('');

  useEffect(() => {
    api.get('/products').then((r) => setProducts(r.data.data));
  }, []);

  const filtered = products.filter(
    (p) =>
      p.name.toLowerCase().includes(search.toLowerCase()) ||
      p.sku.toLowerCase().includes(search.toLowerCase())
  );

  return (
    <AppLayout>
      <div className="flex items-center justify-between mb-6">
        <h1 className="text-xl font-bold text-gray-800">Products</h1>
        <Link
          href="/products/new"
          className="bg-blue-700 text-white px-4 py-2 rounded text-sm font-medium hover:bg-blue-800"
        >
          + New Product
        </Link>
      </div>

      <div className="relative max-w-sm mb-4">
        <div className="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="w-4 h-4">
            <path strokeLinecap="round" strokeLinejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
          </svg>
        </div>
        <input
          type="text"
          placeholder="Search by name or SKU…"
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          className="w-full border border-gray-300 rounded-lg pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-400 transition-colors"
        />
      </div>

      <div className="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
        <table className="w-full text-sm">
          <thead className="bg-gray-50 border-b border-gray-200">
            <tr>
              {['Name', 'SKU', 'Category', 'Price', 'Stock', 'Status', ''].map((h) => (
                <th key={h} className="px-4 py-3 text-left text-sm font-semibold text-gray-700">
                  {h}
                </th>
              ))}
            </tr>
          </thead>
          <tbody>
            {filtered.map((p) => (
              <tr key={p.id} className="border-t border-gray-100 hover:bg-gray-50">
                <td className="px-4 py-3 font-medium text-gray-800">{p.name}</td>
                <td className="px-4 py-3 text-gray-600">{p.sku}</td>
                <td className="px-4 py-3 text-gray-600">{p.category?.name ?? '—'}</td>
                <td className="px-4 py-3 text-gray-700">Rp {Number(p.price).toLocaleString()}</td>
                <td className="px-4 py-3 text-gray-700">{p.stock}</td>
                <td className="px-4 py-3">
                  {p.stock <= p.threshold ? (
                    <span className="bg-red-100 text-red-700 px-2 py-0.5 rounded-full text-xs font-medium">
                      Low Stock
                    </span>
                  ) : (
                    <span className="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs font-medium">
                      OK
                    </span>
                  )}
                </td>
                <td className="px-4 py-3">
                  <Link href={`/products/${p.id}`} className="text-blue-600 hover:underline text-xs">
                    Detail
                  </Link>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
        {filtered.length === 0 && (
          <p className="text-center text-gray-400 py-6 text-sm">No products found</p>
        )}
      </div>
    </AppLayout>
  );
}
