'use client';

import Link from 'next/link';
import { usePathname, useRouter } from 'next/navigation';
import { useEffect, useState } from 'react';

const navItems = [
  { href: '/dashboard', label: 'Dashboard' },
  { href: '/products', label: 'Products' },
  { href: '/stock-in', label: 'Stock In' },
  { href: '/stock-out', label: 'Stock Out' },
  { href: '/notifications', label: 'Notifications' },
];

export default function AppLayout({ children }: { children: React.ReactNode }) {
  const router   = useRouter();
  const pathname = usePathname();
  const [ready, setReady] = useState(false);
  const [user, setUser]   = useState<{ name: string; role: string } | null>(null);

  useEffect(() => {
    const token = localStorage.getItem('token');
    if (!token) {
      router.push('/login');
      return;
    }
    const stored = localStorage.getItem('user');
    if (stored) setUser(JSON.parse(stored));
    setReady(true);
  }, [router]);

  const logout = () => {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    router.push('/login');
  };

  if (!ready) {
    return (
      <div className="flex h-screen items-center justify-center text-gray-500">
        Loading…
      </div>
    );
  }

  return (
    <div className="flex h-screen bg-gray-100">
      {/* Sidebar */}
      <aside className="w-56 bg-blue-800 text-white flex flex-col">
        <div className="px-4 py-5 text-xl font-bold border-b border-blue-700">
          StockFlow
        </div>
        <nav className="flex-1 mt-2">
          {navItems.map(({ href, label }) => (
            <Link
              key={href}
              href={href}
              className={`block px-4 py-2.5 text-sm hover:bg-blue-700 transition-colors ${
                pathname.startsWith(href) ? 'bg-blue-700 font-semibold' : ''
              }`}
            >
              {label}
            </Link>
          ))}
        </nav>
        <div className="px-4 py-3 border-t border-blue-700 text-xs">
          <p className="font-medium">{user?.name}</p>
          <p className="text-blue-300">{user?.role}</p>
          <button
            onClick={logout}
            className="mt-2 text-blue-300 hover:text-white underline"
          >
            Logout
          </button>
        </div>
      </aside>

      {/* Main content */}
      <main className="flex-1 overflow-auto p-6">{children}</main>
    </div>
  );
}
