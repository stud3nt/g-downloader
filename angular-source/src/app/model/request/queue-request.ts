import { DownloadingStatus } from "../../enum/downloading-status";
import { ParsedFile } from "../parsed-file";

export class QueueRequest {

    constructor(obj?: any) {
        Object.assign(this, obj);

        this._files = [];

        if (typeof obj.files !== 'undefined') {
            for (let file in obj.files) {
                this._files.push(new ParsedFile(obj.files[file]));
            }
        }
    }

    private _status: string = DownloadingStatus.Idle;

    private _processingFilesCount: number = 30;

    private _totalFilesCount: number = 0;

    private _files: ParsedFile[] = []

    get files(): ParsedFile[] {
        return this._files;
    }

    set files(value: ParsedFile[]) {
        this._files = value;
    }

    get status(): string {
        return this._status;
    }

    set status(value: string) {
        this._status = value;
    }

    get processingFilesCount(): number {
        return this._processingFilesCount;
    }

    set processingFilesCount(value: number) {
        this._processingFilesCount = value;
    }

    get totalFilesCount(): number {
        return this._totalFilesCount;
    }

    set totalFilesCount(value: number) {
        this._totalFilesCount = value;
    }

    public statusWaiting(): boolean {
        return (this.status !== DownloadingStatus.Downloading && this.status !== DownloadingStatus.Idle);
    }

    public statusDownloading(): boolean {
        return (this.status === DownloadingStatus.Downloading);
    }

    public statusIdle(): boolean {
        return (this.status === DownloadingStatus.Idle);
    }
}
