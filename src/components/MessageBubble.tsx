'use client';

import { cn } from '@/lib/utils';
import { UserIcon, BotIcon } from './icons/PensionIcon';

export interface Message {
  id: string;
  role: 'user' | 'assistant';
  content: string;
  timestamp: Date;
}

interface MessageBubbleProps {
  message: Message;
}

export function MessageBubble({ message }: MessageBubbleProps) {
  const isUser = message.role === 'user';

  return (
    <div
      className={cn(
        'flex gap-3 max-w-[85%] md:max-w-[75%] animate-in fade-in-0 slide-in-from-bottom-2 duration-300',
        isUser ? 'ml-auto flex-row-reverse' : 'mr-auto'
      )}
    >
      <div
        className={cn(
          'flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center shadow-sm',
          isUser
            ? 'bg-slate-700 text-white'
            : 'bg-gradient-to-br from-amber-400 to-amber-500 text-white'
        )}
      >
        {isUser ? (
          <UserIcon className="w-4 h-4" />
        ) : (
          <BotIcon className="w-4 h-4" />
        )}
      </div>

      <div
        className={cn(
          'px-4 py-3 rounded-2xl shadow-sm',
          isUser
            ? 'bg-slate-700 text-white rounded-tr-md'
            : 'bg-white border border-slate-200 text-slate-700 rounded-tl-md'
        )}
      >
        <p className="text-sm md:text-base leading-relaxed whitespace-pre-wrap">
          {message.content}
        </p>
        <span
          className={cn(
            'text-xs mt-2 block',
            isUser ? 'text-slate-300' : 'text-slate-400'
          )}
        >
          {message.timestamp.toLocaleTimeString('es-CL', {
            hour: '2-digit',
            minute: '2-digit',
          })}
        </span>
      </div>
    </div>
  );
}

export function TypingIndicator() {
  return (
    <div className="flex gap-3 max-w-[75%] mr-auto animate-in fade-in-0 duration-300">
      <div className="flex-shrink-0 w-8 h-8 rounded-full bg-gradient-to-br from-amber-400 to-amber-500 text-white flex items-center justify-center shadow-sm">
        <BotIcon className="w-4 h-4" />
      </div>
      <div className="px-4 py-3 rounded-2xl bg-white border border-slate-200 rounded-tl-md shadow-sm">
        <div className="flex gap-1 items-center">
          <div className="w-2 h-2 bg-slate-400 rounded-full animate-bounce" style={{ animationDelay: '0ms' }} />
          <div className="w-2 h-2 bg-slate-400 rounded-full animate-bounce" style={{ animationDelay: '150ms' }} />
          <div className="w-2 h-2 bg-slate-400 rounded-full animate-bounce" style={{ animationDelay: '300ms' }} />
        </div>
      </div>
    </div>
  );
}
