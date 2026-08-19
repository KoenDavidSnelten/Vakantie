<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-5 py-2.5 bg-sky-600 border border-transparent rounded-lg font-semibold text-sm text-white shadow-sm hover:bg-sky-500 focus:bg-sky-500 active:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
