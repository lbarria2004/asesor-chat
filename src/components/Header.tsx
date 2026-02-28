'use client';

import { PensionIcon } from './icons/PensionIcon';

export function Header() {
  return (
    <header className="bg-gradient-to-r from-slate-800 to-slate-700 text-white shadow-lg">
      <div className="max-w-4xl mx-auto px-4 py-4">
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-3">
            <div className="bg-white/10 p-2 rounded-lg">
              <PensionIcon className="w-8 h-8 text-amber-400" />
            </div>
            <div>
              <h1 className="text-xl md:text-2xl font-bold tracking-tight">
                PensionVox Chile
              </h1>
              <p className="text-xs md:text-sm text-slate-300">
                Asesoría Previsional Experta
              </p>
            </div>
          </div>
          <div className="hidden md:flex items-center gap-4 text-sm">
            <div className="flex items-center gap-2 bg-emerald-500/20 px-3 py-1.5 rounded-full">
              <span className="w-2 h-2 bg-emerald-400 rounded-full animate-pulse" />
              <span className="text-emerald-300 font-medium">En línea</span>
            </div>
            <a 
              href="tel:+56912345678" 
              className="text-slate-300 hover:text-white transition-colors"
            >
              +56 9 1234 5678
            </a>
          </div>
        </div>
      </div>
    </header>
  );
}
