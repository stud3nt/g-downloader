import { DownloaderStatus as DownloaderStatusEnum } from "../enum/downloader-status";
import { ParsedFile } from "./parsed-file";

export class DownloadStatus {

	constructor(obj?: any) {
		Object.assign(this, obj);

		this._queuedFiles = [];

		if (obj) {
			if (obj.queuedFiles)
				for (let qFile of obj.queuedFiles)
					this._queuedFiles.push(new ParsedFile(qFile));
		}
	}

	private _status: string = DownloaderStatusEnum.Idle;

	private _queuedFilesCount: number = 0;

	private _queuedFilestSize: number = 0;

	private _queuedFilesTextSize: string = '';

	private _downloadedFilesCount: number = 0;

	private _downloadedFilesSize: number = 0;

	private _downloadedFilesTextSize: string = '';

	private _queuedFiles: ParsedFile[] = [];

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

	get queuedFiles(): ParsedFile[] {
		return this._queuedFiles;
	}

	set queuedFiles(value: ParsedFile[]) {
		this._queuedFiles = value;
	}

	get queuedFilestSize(): number {
		return this._queuedFilestSize;
	}

	set queuedFilestSize(value: number) {
		this._queuedFilestSize = value;
	}

	get queuedFilesTextSize(): string {
		return this._queuedFilesTextSize;
	}

	set queuedFilesTextSize(value: string) {
		this._queuedFilesTextSize = value;
	}

	get downloadedFilesSize(): number {
		return this._downloadedFilesSize;
	}

	set downloadedFilesSize(value: number) {
		this._downloadedFilesSize = value;
	}

	get downloadedFilesTextSize(): string {
		return this._downloadedFilesTextSize;
	}

	set downloadedFilesTextSize(value: string) {
		this._downloadedFilesTextSize = value;
	}
}