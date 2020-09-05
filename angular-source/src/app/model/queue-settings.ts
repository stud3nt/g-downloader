import { DownloaderStatus as DownloaderStatusEnum } from "../enum/downloader-status";

export class QueueSettings {

	constructor(obj?: any) {
		Object.assign(this, obj);
	}

	private _status: string = DownloaderStatusEnum.Idle;

	private _queuedFilesCount: number = 0;

	private _downloadedFilesCount: number = 0;

	private _processedQueueSize: number = 30;

	private _processedFileCount: number = 5;

    get status(): string {
        return this._status;
    }

    set status(value: string) {
        this._status = value;
    }

    get queuedFilesCount(): number {
        return this._queuedFilesCount;
    }

    set queuedFilesCount(value: number) {
        this._queuedFilesCount = value;
    }

    get downloadedFilesCount(): number {
        return this._downloadedFilesCount;
    }

    set downloadedFilesCount(value: number) {
        this._downloadedFilesCount = value;
    }

    get processedQueueSize(): number {
        return this._processedQueueSize;
    }

    set processedQueueSize(value: number) {
        this._processedQueueSize = value;
    }

    get processedFileCount(): number {
        return this._processedFileCount;
    }

    set processedFileCount(value: number) {
        this._processedFileCount = value;
    }
}
