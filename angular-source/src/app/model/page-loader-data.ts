export class PageLoaderData {

	constructor(obj?: any) {
		Object.assign(this, obj);
	}

	private _visible: boolean = false;

	// percentage progress status
	private _progress: number = 0;

	// auto close after finish (progress = 100)
	private _autoClose: boolean = true;

	// timeout for closing after finish;
	private _autoCloseTimeout: number = 200;

	public reset() {
		this._visible = false;
		this._progress = 0;
		this._autoClose = true;
	}

    get visible(): boolean {
        return this._visible;
    }

    set visible(value: boolean) {
        this._visible = value;
    }

    get progress(): number {
        return this._progress;
    }

    set progress(value: number) {
        this._progress = value;
    }

    get autoClose(): boolean {
        return this._autoClose;
    }

    set autoClose(value: boolean) {
        this._autoClose = value;
    }

    get autoCloseTimeout(): number {
        return this._autoCloseTimeout;
    }

    set autoCloseTimeout(value: number) {
        this._autoCloseTimeout = value;
    }

}
