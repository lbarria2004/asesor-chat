'use client';

import { Button } from '@/components/ui/button';

interface QuickActionsProps {
  onAction: (question: string) => void;
  disabled?: boolean;
}

const quickQuestions = [
  {
    id: 1,
    question: '¿Qué tipos de pensión existen?',
    icon: '💼',
  },
  {
    id: 2,
    question: '¿Cuál es la mejor AFP?',
    icon: '📊',
  },
  {
    id: 3,
    question: '¿Qué es la PGU?',
    icon: '🛡️',
  },
  {
    id: 4,
    question: '¿Cómo puedo mejorar mi pensión?',
    icon: '📈',
  },
];

export function QuickActions({ onAction, disabled }: QuickActionsProps) {
  return (
    <div className="px-4 py-3 bg-slate-50 border-t border-slate-100">
      <p className="text-xs text-slate-500 mb-2 text-center">
        Preguntas frecuentes
      </p>
      <div className="flex flex-wrap gap-2 justify-center">
        {quickQuestions.map((q) => (
          <Button
            key={q.id}
            variant="outline"
            size="sm"
            onClick={() => onAction(q.question)}
            disabled={disabled}
            className="bg-white hover:bg-slate-100 border-slate-200 text-slate-700 text-xs md:text-sm transition-all hover:shadow-md"
          >
            <span className="mr-1">{q.icon}</span>
            {q.question}
          </Button>
        ))}
      </div>
    </div>
  );
}
