import { test } from 'node:test';
import assert from 'node:assert/strict';
import applicationBoard from '../resources/js/application-board.js';

const card = () => ({ id: 1, company: 'Acme', status: 'saved', moveUrl: '/applications/1/stage' });

test('moving a card saves by AJAX and updates stage counts without navigation', async () => {
    const original = globalThis.fetch;
    const board = applicationBoard([card()], 'csrf-token');
    globalThis.fetch = async (url, options) => {
        assert.equal(url, '/applications/1/stage');
        assert.equal(options.method, 'PATCH');
        assert.equal(options.headers['X-CSRF-TOKEN'], 'csrf-token');
        assert.equal(options.headers.Accept, 'application/json');
        assert.deepEqual(JSON.parse(options.body), { status: 'interview' });
        assert.equal(board.inStage('saved').length, 1);
        return { ok: true, json: async () => ({ id: 1, status: 'interview' }) };
    };
    try {
        await board.move(1, 'interview');
        assert.equal(board.inStage('saved').length, 0);
        assert.equal(board.inStage('interview').length, 1);
        assert.equal(board.pending, null);
        assert.equal(board.error, '');
    } finally {
        globalThis.fetch = original;
    }
});

test('failed saves keep the card in its original stage and allow retry', async () => {
    const original = globalThis.fetch;
    const board = applicationBoard([card()], 'token');
    globalThis.fetch = async () => ({ ok: false, status: 500 });
    try {
        await board.move(1, 'offer');
        assert.equal(board.applications[0].status, 'saved');
        assert.match(board.error, /try again/);
        assert.equal(board.pending, null);
        globalThis.fetch = async () => ({ ok: true, json: async () => ({ id: 1, status: 'offer' }) });
        await board.move(1, 'offer');
        assert.equal(board.applications[0].status, 'offer');
        assert.equal(board.error, '');
    } finally {
        globalThis.fetch = original;
    }
});

test('expired sessions and network failures do not move cards', async () => {
    const original = globalThis.fetch;
    const board = applicationBoard([card()], 'token');
    try {
        globalThis.fetch = async () => ({ ok: false, status: 419 });
        await board.move(1, 'offer');
        assert.match(board.error, /session has expired/);
        globalThis.fetch = async () => { throw new TypeError('Failed to fetch'); };
        await board.move(1, 'offer');
        assert.match(board.error, /Connection lost/);
        assert.equal(board.applications[0].status, 'saved');
    } finally {
        globalThis.fetch = original;
    }
});

test('pending requests block duplicate moves', async () => {
    const original = globalThis.fetch;
    const board = applicationBoard([card()], 'token');
    let complete;
    let requests = 0;
    globalThis.fetch = () => {
        requests++;
        return new Promise((resolve) => { complete = resolve; });
    };
    try {
        const moving = board.move(1, 'applied');
        await board.move(1, 'offer');
        assert.equal(requests, 1);
        complete({ ok: true, json: async () => ({ id: 1, status: 'applied' }) });
        await moving;
        assert.equal(board.applications[0].status, 'applied');
    } finally {
        globalThis.fetch = original;
    }
});
