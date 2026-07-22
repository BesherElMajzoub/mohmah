{{-- Floating conversion actions.

     Present at every breakpoint and on every page. This is not a popup: it
     covers no content, appears immediately rather than ambushing the reader
     after a delay, and there is nothing to dismiss.

     Restraint is deliberate — the identity rules out loud gold fills, so the
     call button is ink with a fine gold ring and WhatsApp is ivory with a
     gold border. On desktop each expands to reveal its label on hover or
     keyboard focus; on mobile they stay compact circles to keep the reading
     column clear.

     The buttons are ordinary anchors carrying data-track. If JavaScript
     fails, they are still working phone and WhatsApp links. --}}

<div class="fixed bottom-5 z-50 flex flex-col gap-3 end-4 sm:bottom-7 sm:end-6"
     style="bottom: calc(1.25rem + env(safe-area-inset-bottom));">

    {{-- WhatsApp — placed above so the more common action (call) sits
         closest to the thumb. --}}
    <a href="{{ config('site.whatsapp_href') }}"
       target="_blank"
       rel="noopener"
       data-track="whatsapp"
       data-placement="floating"
       class="group flex h-14 items-center gap-0 overflow-hidden rounded-full border border-gold
              bg-ivory text-ink shadow-lg transition-all duration-300 ease-[cubic-bezier(0.22,0.61,0.36,1)]
              hover:gap-3 hover:bg-ink hover:text-ivory hover:shadow-xl
              focus-visible:gap-3 focus-visible:bg-ink focus-visible:text-ivory
              w-14 hover:w-auto hover:ps-6 focus-visible:w-auto focus-visible:ps-6">
        <span class="grid size-14 shrink-0 place-items-center">
            <svg class="size-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38a9.9 9.9 0 0 0 4.79 1.22h.01c5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2Zm0 18.15h-.01a8.23 8.23 0 0 1-4.19-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.19 8.19 0 0 1-1.26-4.38c0-4.54 3.7-8.24 8.25-8.24 2.2 0 4.27.86 5.83 2.42a8.19 8.19 0 0 1 2.41 5.83c0 4.54-3.7 8.23-8.24 8.23Zm4.52-6.16c-.25-.12-1.47-.72-1.69-.81-.23-.08-.39-.12-.56.13-.16.24-.64.8-.79.97-.14.16-.29.18-.54.06-.25-.12-1.05-.39-1.99-1.23-.74-.66-1.23-1.47-1.38-1.72-.14-.25-.01-.38.11-.5.11-.11.25-.29.37-.43.13-.15.17-.25.25-.41.08-.17.04-.31-.02-.43-.06-.12-.56-1.34-.76-1.84-.2-.48-.41-.42-.56-.43h-.48c-.17 0-.43.06-.66.31-.22.25-.87.85-.87 2.07s.89 2.4 1.02 2.56c.12.17 1.75 2.67 4.23 3.74.59.26 1.05.41 1.41.52.59.19 1.13.16 1.56.1.48-.07 1.47-.6 1.67-1.18.21-.58.21-1.08.14-1.18-.06-.11-.22-.17-.47-.29Z"/>
            </svg>
        </span>

        {{-- The label is a real element rather than a tooltip so it is read
             by assistive tech and works without hover. --}}
        <span class="hidden whitespace-nowrap font-display text-sm lg:block lg:max-w-0 lg:opacity-0
                     lg:transition-all lg:duration-300
                     lg:group-hover:max-w-[12rem] lg:group-hover:pe-6 lg:group-hover:opacity-100
                     lg:group-focus-visible:max-w-[12rem] lg:group-focus-visible:pe-6 lg:group-focus-visible:opacity-100">
            راسلنا عبر واتساب
        </span>

        <span class="sr-only lg:hidden">راسلنا عبر واتساب</span>
    </a>

    {{-- Call --}}
    <a href="{{ config('site.tel_href') }}"
       data-track="call"
       data-placement="floating"
       class="group flex h-14 items-center gap-0 overflow-hidden rounded-full border border-gold/60
              bg-ink text-ivory shadow-lg transition-all duration-300 ease-[cubic-bezier(0.22,0.61,0.36,1)]
              hover:gap-3 hover:border-gold hover:bg-ink-700 hover:shadow-xl
              focus-visible:gap-3 focus-visible:border-gold
              w-14 hover:w-auto hover:ps-6 focus-visible:w-auto focus-visible:ps-6">
        <span class="grid size-14 shrink-0 place-items-center">
            <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92Z"/>
            </svg>
        </span>

        <span class="hidden whitespace-nowrap font-display text-sm lg:block lg:max-w-0 lg:opacity-0
                     lg:transition-all lg:duration-300
                     lg:group-hover:max-w-[12rem] lg:group-hover:pe-6 lg:group-hover:opacity-100
                     lg:group-focus-visible:max-w-[12rem] lg:group-focus-visible:pe-6 lg:group-focus-visible:opacity-100">
            اتصل بالمكتب
        </span>

        <span class="sr-only lg:hidden">اتصل بالمكتب</span>
    </a>
</div>
